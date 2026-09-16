<?php

namespace App\Services;

class SimpleZipWriter
{
    private array $files = [];

    public function addFile(string $name, string $data): void
    {
        $this->files[] = [
            'name' => $name,
            'data' => $data,
            'crc' => crc32($data),
            'length' => strlen($data),
        ];
    }

    public function getZipContent(): string
    {
        $zipData = '';
        $cd = '';
        $offset = 0;

        foreach ($this->files as $file) {
            $nameLen = strlen($file['name']);
            $crc = $file['crc'];
            $len = $file['length'];
            $dosTimeDate = "\x00\x00\x21\x58"; // 2024-01-01 00:00:00 DOS timestamp

            // Local file header (30 bytes + name)
            $header = "\x50\x4b\x03\x04" . // Local file header signature (0x04034b50)
                      "\x0a\x00" .         // Version needed to extract (1.0)
                      "\x00\x00" .         // General purpose bit flag
                      "\x00\x00" .         // Compression method (0 = STORED / uncompressed)
                      $dosTimeDate .       // Last mod file time and date
                      pack('V', $crc) .    // CRC-32
                      pack('V', $len) .    // Compressed size
                      pack('V', $len) .    // Uncompressed size
                      pack('v', $nameLen) .// File name length
                      "\x00\x00" .         // Extra field length
                      $file['name'];

            $zipData .= $header . $file['data'];

            // Central directory header (46 bytes + name)
            $cdEntry = "\x50\x4b\x01\x02" . // Central file header signature (0x02014b50)
                       "\x14\x00" .         // Version made by (2.0)
                       "\x0a\x00" .         // Version needed to extract (1.0)
                       "\x00\x00" .         // General purpose bit flag
                       "\x00\x00" .         // Compression method (0 = STORED)
                       $dosTimeDate .       // Last mod file time and date
                       pack('V', $crc) .    // CRC-32
                       pack('V', $len) .    // Compressed size
                       pack('V', $len) .    // Uncompressed size
                       pack('v', $nameLen) .// File name length
                       "\x00\x00" .         // Extra field length
                       "\x00\x00" .         // File comment length
                       "\x00\x00" .         // Disk number start
                       "\x00\x00" .         // Internal file attributes
                       "\x20\x00\x00\x00" . // External file attributes
                       pack('V', $offset) . // Relative offset of local header
                       $file['name'];


            $cd .= $cdEntry;
            $offset += strlen($header) + $len;
        }

        $cdLen = strlen($cd);

        // End of central directory record (22 bytes)
        $eocd = "\x50\x4b\x05\x06" . // End of central dir signature (0x06054b50)
                "\x00\x00" .         // Number of this disk
                "\x00\x00" .         // Disk where central directory starts
                pack('v', count($this->files)) . // Number of central directory records on this disk
                pack('v', count($this->files)) . // Total number of central directory records
                pack('V', $cdLen) .  // Size of central directory (bytes)
                pack('V', $offset) . // Offset of start of central directory
                "\x00\x00";          // Comment length

        return $zipData . $cd . $eocd;
    }
}
