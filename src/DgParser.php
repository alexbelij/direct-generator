<?php

namespace skobka\dg;

use SplFileObject;

/**
 * Парсер .dg файлов
 * .dg файл содержит следующую структуру
 * <code>
 * [Ключи]
 * ключевые слова
 * каждог с новой строки
 *
 * [Заголовки]
 * Текст заголовка с указанием ключа как [key]
 * [Key] если указать ключ с большой буквы, то он будет подставлен с заглавной
 *
 * [Тексты]
 * Формат аналогичен секции Заголовков
 * </code>
 */
class DgParser implements Parser
{
    const FLAG_KEYWORDS = 1;
    const FLAG_TITLES = 2;
    const FLAG_TEXTS = 3;

    const MARKER_KEYWORDS = '[Ключи]';
    const MARKER_TITLES = '[Заголовки]';
    const MARKER_TEXTS = '[Тексты]';

    /**
     * @var \SplFileObject
     */
    private $file;

    /**
     * @var string[]
     */
    private $keywords = [];
    /**
     * @var string[]
     */
    private $titles = [];
    /**
     * @var string[]
     */
    private $texts = [];

    /**
     * @var int
     */
    private $flag;

    /**
     * @param string $file
     * @return void
     */
    public function parse(string $file)
    {
        $this->setFile($file);

        $markers = $this->getMarkers();
        $flags = $this->getFlags();

        while (!$this->file->eof()) {
            $line = $this->file->fgets();
            $text = trim($line);
            if (!$text) {
                continue;
            }

            $key = array_search($text, $markers);
            if ($key !== false) {
                $this->flag = $flags[$key];
                continue;
            }

            if (!$this->flag) {
                continue;
            }

            $this->addText($text, $this->flag);
        }
    }

    /**
     * @return string[]
     */
    public function getKeywords(): array
    {
        return $this->keywords;
    }

    /**
     * @return string[]
     */
    public function getTitles(): array
    {
        return $this->titles;
    }

    /**
     * @return string[]
     */
    public function getTexts(): array
    {
        return $this->texts;
    }

    /**
     * @return array
     */
    private function getMarkers(): array
    {
        return [self::MARKER_KEYWORDS, self::MARKER_TITLES, self::MARKER_TEXTS];
    }

    /**
     * @return array
     */
    private function getFlags(): array
    {
        return [self::FLAG_KEYWORDS, self::FLAG_TITLES, self::FLAG_TEXTS];
    }

    /**
     * @param string $text
     * @param int $flag
     */
    private function addText(string $text, int $flag)
    {
        if ($flag === self::FLAG_KEYWORDS) {
            $this->keywords[] = $text;
        } elseif ($flag === self::FLAG_TITLES) {
            $this->titles[] = $text;
        } elseif ($flag === self::FLAG_TEXTS) {
            $this->texts[] = $text;
        }
    }

    /**
     * @param string $file
     */
    private function setFile(string $file): void
    {
        $this->file = new SplFileObject($file);
    }
}
