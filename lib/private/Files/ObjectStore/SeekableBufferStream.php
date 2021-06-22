<?php

namespace OC\Files\ObjectStore;

use Psr\Http\Message\StreamInterface;

/**
 * Provides a buffer stream that can be written to to fill a buffer, and read
 * from to remove bytes from the buffer.
 *
 * This stream returns a "hwm" metadata value that tells upstream consumers
 * what the configured high water mark of the stream is, or the maximum
 * preferred size of the buffer.
 */
class SeekableBufferStream implements StreamInterface
{
    private $membuffer;

    /**
     * @param int $hwm High water mark, representing the preferred maximum
     *                 buffer size. If the size of the buffer exceeds the high
     *                 water mark, then calls to write will continue to succeed
     *                 but will return false to inform writers to slow down
     *                 until the buffer has been drained by reading from it.
     */
    public function __construct()
    {
        $this->membuffer = fopen("php://memory", "rw+");
    }

    public function __toString()
    {
        return $this->getContents();
    }

    public function getContents()
    {
        $remaining = $this->getSize() - ftell($this->membuffer);
        return fread($this->membuffer,  $remaining);
    }

    public function close()
    {
        fclose($this->membuffer);
    }

    public function detach()
    {
        $this->close();

        return null;
    }

    public function getSize()
    {
        $stats = fstat($this->membuffer);
        return $stats['size'];
    }

    public function isReadable()
    {
        return true;
    }

    public function isWritable()
    {
        return true;
    }

    public function isSeekable()
    {
        return true;
    }

    public function rewind()
    {
        rewind($this->membuffer);
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        fseek($this->membuffer, $offset, $whence);
    }

    public function eof()
    {
        return feof($this->membuffer);
    }

    public function tell()
    {
        return ftell($this->membuffer);
    }

    /**
     * Reads data from the buffer.
     */
    public function read($length)
    {
        return fread($this->membuffer, $length);
    }

    /**
     * Writes data to the buffer.
     */
    public function write($string)
    {
        return fwrite($this->membuffer, $string);
    }

    public function getMetadata($key = null)
    {
        return null;
    }
}
