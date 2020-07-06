<?php

namespace Pilipinews\Website\Cnn;

use Pilipinews\Common\Client;
use Pilipinews\Common\Crawler as DomCrawler;
use Pilipinews\Common\Interfaces\CrawlerInterface;

/**
 * CNN Philippines Crawler
 *
 * @package Pilipinews
 * @author  Rougin Gutib <rougingutib@gmail.com>
 */
class Crawler implements CrawlerInterface
{
    /**
     * Returns an array of articles to scrape.
     *
     * @return string[]
     */
    public function crawl()
    {
        $base = 'https://cnnphilippines.com';

        $response = Client::request($base);

        $callback = function (DomCrawler $node) use ($base)
        {
            return $base . $node->filter('a')->attr('href');
        };

        $crawler = new DomCrawler((string) $response);

        $news = $crawler->filter('.cbwidget-list > li');

        $news = $this->verify($news->each($callback));

        return array_reverse((array) $news);
    }

    /**
     * Returns the allowed article URLs to scrape.
     *
     * @param  string[] $items
     * @return string[]
     */
    protected function verify($items)
    {
        $callback = function ($link)
        {
            return strpos($link, '/news/') !== false ? $link : null;
        };

        $items = array_map($callback, (array) $items);

        return array_values(array_filter($items));
    }
}
