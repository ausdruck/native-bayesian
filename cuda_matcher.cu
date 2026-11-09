#include <cuda_runtime.h>

#include <chrono>
#include <cstdint>
#include <cstdlib>
#include <fstream>
#include <iostream>
#include <limits>
#include <stdexcept>
#include <string>
#include <vector>

#define CUDA_CHECK(call)                                                     \
    do {                                                                     \
        cudaError_t err__ = (call);                                          \
        if (err__ != cudaSuccess) {                                          \
            throw std::runtime_error(std::string(#call) + ": " +             \
                                     cudaGetErrorString(err__));              \
        }                                                                    \
    } while (0)

struct TextBuffer {
    std::vector<unsigned char> bytes;
    std::vector<std::uint64_t> offsets;
    std::vector<std::uint32_t> lengths;
};

static TextBuffer read_lines(const char* path) {
    std::ifstream input(path, std::ios::binary);
    if (!input) {
        throw std::runtime_error(std::string("Cannot open: ") + path);
    }

    TextBuffer result;
    std::string line;

    while (std::getline(input, line)) {
        if (line.size() > std::numeric_limits<std::uint32_t>::max()) {
            throw std::runtime_error("A line is longer than UINT32_MAX");
        }

        result.offsets.push_back(
            static_cast<std::uint64_t>(result.bytes.size()));
        result.lengths.push_back(static_cast<std::uint32_t>(line.size()));
        result.bytes.insert(result.bytes.end(), line.begin(), line.end());
    }

    if (input.bad()) {
        throw std::runtime_error(std::string("Read error: ") + path);
    }

    return result;
}

__device__ __forceinline__
bool byte_substring(
    const unsigned char* text,
    std::uint32_t text_length,
    const unsigned char* word,
    std::uint32_t word_length) {

    // PHP strpos($text, "") returns 0: an empty dictionary entry matches.
    if (word_length == 0) {
        return true;
    }
    if (word_length > text_length) {
        return false;
    }

    const std::uint32_t last = text_length - word_length;
    for (std::uint32_t pos = 0; pos <= last; ++pos) {
        std::uint32_t j = 0;
        while (j < word_length && text[pos + j] == word[j]) {
            ++j;
        }
        if (j == word_length) {
            return true;
        }
    }
    return false;
}

__global__
void match_kernel(
    const unsigned char* samples,
    const std::uint64_t* sample_offsets,
    const std::uint32_t* sample_lengths,
    std::uint32_t sample_count,
    const unsigned char* dictionary,
    const std::uint64_t* dictionary_offsets,
    const std::uint32_t* dictionary_lengths,
    std::uint32_t dictionary_count,
    unsigned char* matches,
    std::uint64_t pair_count) {

    const std::uint64_t pair =
        static_cast<std::uint64_t>(blockIdx.x) * blockDim.x + threadIdx.x;

    if (pair >= pair_count) {
        return;
    }

    const std::uint32_t sample_index =
        static_cast<std::uint32_t>(pair / dictionary_count);
    const std::uint32_t word_index =
        static_cast<std::uint32_t>(pair % dictionary_count);

    const unsigned char* sample =
        samples + sample_offsets[sample_index];
    const unsigned char* word =
        dictionary + dictionary_offsets[word_index];

    matches[pair] = byte_substring(
        sample,
        sample_lengths[sample_index],
        word,
        dictionary_lengths[word_index]) ? 1 : 0;
}

static void copy_to_device(
    const void* source,
    std::size_t bytes,
    void** destination) {

    if (bytes == 0) {
        *destination = nullptr;
        return;
    }

    CUDA_CHECK(cudaMalloc(destination, bytes));
    CUDA_CHECK(cudaMemcpy(
        *destination, source, bytes, cudaMemcpyHostToDevice));
}

int main(int argc, char** argv) {
    if (argc != 4) {
        std::cerr
            << "Usage: " << argv[0]
            << " <dictionary.txt> <normalized-samples.txt> <matches.tsv>\n"
            << "Output rows: sample_index<TAB>dictionary_index\n";
        return 2;
    }

    try {
        const TextBuffer dictionary = read_lines(argv[1]);
        const TextBuffer samples = read_lines(argv[2]);

        if (dictionary.offsets.empty()) {
            throw std::runtime_error("Dictionary contains no lines");
        }
        if (samples.offsets.empty()) {
            throw std::runtime_error("Samples file contains no lines");
        }

        if (dictionary.offsets.size() >
                std::numeric_limits<std::uint32_t>::max() ||
            samples.offsets.size() >
                std::numeric_limits<std::uint32_t>::max()) {
            throw std::runtime_error("Too many dictionary entries or samples");
        }

        const std::uint64_t sample_count = samples.offsets.size();
        const std::uint64_t dictionary_count = dictionary.offsets.size();

        if (sample_count >
            std::numeric_limits<std::uint64_t>::max() / dictionary_count) {
            throw std::runtime_error("Pair count overflow");
        }

        const std::uint64_t pair_count =
            sample_count * dictionary_count;

        if (pair_count > std::numeric_limits<std::size_t>::max()) {
            throw std::runtime_error("Match matrix is too large for this host");
        }

        int device = 0;
        CUDA_CHECK(cudaSetDevice(device));

        cudaDeviceProp properties{};
        CUDA_CHECK(cudaGetDeviceProperties(&properties, device));

        unsigned char* d_samples = nullptr;
        std::uint64_t* d_sample_offsets = nullptr;
        std::uint32_t* d_sample_lengths = nullptr;
        unsigned char* d_dictionary = nullptr;
        std::uint64_t* d_dictionary_offsets = nullptr;
        std::uint32_t* d_dictionary_lengths = nullptr;
        unsigned char* d_matches = nullptr;

        const std::size_t match_bytes =
            static_cast<std::size_t>(pair_count);

        try {
            copy_to_device(
                samples.bytes.data(), samples.bytes.size(),
                reinterpret_cast<void**>(&d_samples));
            copy_to_device(
                samples.offsets.data(),
                samples.offsets.size() * sizeof(std::uint64_t),
                reinterpret_cast<void**>(&d_sample_offsets));
            copy_to_device(
                samples.lengths.data(),
                samples.lengths.size() * sizeof(std::uint32_t),
                reinterpret_cast<void**>(&d_sample_lengths));

            copy_to_device(
                dictionary.bytes.data(), dictionary.bytes.size(),
                reinterpret_cast<void**>(&d_dictionary));
            copy_to_device(
                dictionary.offsets.data(),
                dictionary.offsets.size() * sizeof(std::uint64_t),
                reinterpret_cast<void**>(&d_dictionary_offsets));
            copy_to_device(
                dictionary.lengths.data(),
                dictionary.lengths.size() * sizeof(std::uint32_t),
                reinterpret_cast<void**>(&d_dictionary_lengths));

            CUDA_CHECK(cudaMalloc(reinterpret_cast<void**>(&d_matches), match_bytes));

            cudaEvent_t start{}, stop{};
            CUDA_CHECK(cudaEventCreate(&start));
            CUDA_CHECK(cudaEventCreate(&stop));

            constexpr unsigned int threads = 256;
            const std::uint64_t block_count =
                (pair_count + threads - 1) / threads;

            if (block_count > std::numeric_limits<unsigned int>::max()) {
                throw std::runtime_error("CUDA grid dimension is too large");
            }

            CUDA_CHECK(cudaEventRecord(start));
            match_kernel<<<static_cast<unsigned int>(block_count), threads>>>(
                d_samples,
                d_sample_offsets,
                d_sample_lengths,
                static_cast<std::uint32_t>(sample_count),
                d_dictionary,
                d_dictionary_offsets,
                d_dictionary_lengths,
                static_cast<std::uint32_t>(dictionary_count),
                d_matches,
                pair_count);
            CUDA_CHECK(cudaGetLastError());
            CUDA_CHECK(cudaEventRecord(stop));
            CUDA_CHECK(cudaEventSynchronize(stop));

            float kernel_ms = 0.0f;
            CUDA_CHECK(cudaEventElapsedTime(&kernel_ms, start, stop));

            std::vector<unsigned char> matches(match_bytes);
            CUDA_CHECK(cudaMemcpy(
                matches.data(), d_matches, match_bytes,
                cudaMemcpyDeviceToHost));

            std::ofstream output(argv[3], std::ios::binary);
            if (!output) {
                throw std::runtime_error(
                    std::string("Cannot write: ") + argv[3]);
            }

            std::uint64_t hit_count = 0;
            for (std::uint64_t sample_index = 0;
                 sample_index < sample_count; ++sample_index) {
                const std::uint64_t row =
                    sample_index * dictionary_count;

                for (std::uint64_t word_index = 0;
                     word_index < dictionary_count; ++word_index) {
                    if (matches[row + word_index] != 0) {
                        output << sample_index << '\t'
                               << word_index << '\n';
                        ++hit_count;
                    }
                }
            }

            if (!output) {
                throw std::runtime_error("Failed while writing match output");
            }

            CUDA_CHECK(cudaEventDestroy(start));
            CUDA_CHECK(cudaEventDestroy(stop));

            std::cout << "GPU: " << properties.name << '\n'
                      << "Samples: " << sample_count << '\n'
                      << "Dictionary entries: " << dictionary_count << '\n'
                      << "Pairs checked: " << pair_count << '\n'
                      << "Matches: " << hit_count << '\n'
                      << "Kernel time: " << kernel_ms << " ms\n";

        } catch (...) {
            if (d_samples) cudaFree(d_samples);
            if (d_sample_offsets) cudaFree(d_sample_offsets);
            if (d_sample_lengths) cudaFree(d_sample_lengths);
            if (d_dictionary) cudaFree(d_dictionary);
            if (d_dictionary_offsets) cudaFree(d_dictionary_offsets);
            if (d_dictionary_lengths) cudaFree(d_dictionary_lengths);
            if (d_matches) cudaFree(d_matches);
            throw;
        }

        if (d_samples) CUDA_CHECK(cudaFree(d_samples));
        if (d_sample_offsets) CUDA_CHECK(cudaFree(d_sample_offsets));
        if (d_sample_lengths) CUDA_CHECK(cudaFree(d_sample_lengths));
        if (d_dictionary) CUDA_CHECK(cudaFree(d_dictionary));
        if (d_dictionary_offsets) CUDA_CHECK(cudaFree(d_dictionary_offsets));
        if (d_dictionary_lengths) CUDA_CHECK(cudaFree(d_dictionary_lengths));
        if (d_matches) CUDA_CHECK(cudaFree(d_matches));

        return 0;
    } catch (const std::exception& error) {
        std::cerr << "ERROR: " << error.what() << '\n';
        return 1;
    }
}
