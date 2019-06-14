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
        $base = (string) 'https://cnnphilippines.com';

        $url = 'https://cnnphilippines.com/search/?order=DESC';

        $query = '&page=1&q=a&sort=PUBLISHDATE';

        $response = Client::request($url . $query);

        $callback = function (DomCrawler $node) use ($base)
        {
            $link = $node->filter('.media-heading > a');

            return (string) $base . $link->attr('href');
        };

        $crawler = new DomCrawler((string) $response);

        $news = $crawler->filter('.results > .media');

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
