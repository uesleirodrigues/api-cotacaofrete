<?php

class MockPhpStream {
    public static $content;
    private $index;
    private $stream;

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $this->index = 0;
        return true;
    }

    public function stream_read($count)
    {
        $ret = substr(self::$content, $this->index, $count);
        $this->index += strlen($ret);
        return $ret;
    }

    public function stream_eof()
    {
        return $this->index >= strlen(self::$content);
    }

    public function stream_stat()
    {
        return [];
    }

    public function stream_seek($offset, $whence)
    {
        if ($whence === SEEK_SET) {
            $this->index = $offset;
            return true;
        }
        return false;
    }

    public static function registerInput($content)
    {
        self::$content = $content;
    }
}
