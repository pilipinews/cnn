<?php

namespace Pilipinews\Website\Cnn;

use Pilipinews\Common\Article;
use Pilipinews\Common\Client;
use Pilipinews\Common\Crawler as DomCrawler;
use Pilipinews\Common\Interfaces\ScraperInterface;
use Pilipinews\Common\Scraper as AbstractScraper;

/**
 * CNN Philippines Scraper
 *
 * @package Pilipinews
 * @author  Rougin Gutib <rougingutib@gmail.com>
 */
class Scraper extends AbstractScraper implements ScraperInterface
{
    /**
     * @var string[]
     */
    protected $removables = array('p > script', '.flourish-credit');

    /**
     * @var string[]
     */
    protected $reload = array(
        'Please click the source link below for more updates.',
        'Please refresh for updates.',
        'Please refresh the page for updates.',
        'Please refresh this page for updates.',
        'Refresh this page for more updates.',
    );

    /**
     * Returns the contents of an article.
     *
     * @param  string $link
     * @return \Pilipinews\Common\Article
     */
    public function scrape($link)
    {
        $this->prepare((string) $link);

        $title = $this->title('title', ' - CNN Philippines');

        $body = $this->body('#content-body');

        $body = $this->video($this->tweet($body));

        $html = $this->html($body, $this->reload);

        $search = '/pic.twitter.com\/(.*)- CNN/i';

        $replace = (string) 'pic.twitter.com/$1 - CNN';

        $html = preg_replace($search, $replace, $html);

        return new Article($title, $html, $link);
    }

    /**
     * Initializes the crawler instance.
     *
     * @param  string $link
     * @return void
     */
    protected function prepare($link)
    {
        $pattern = '/content-body-[0-9]+(-[0-9]+)+/i';

        $html = Client::request((string) $link);

        $html = str_replace(' </em> ', '</em> ', $html);

        preg_match($pattern, (string) $html, $matches);

        $html = str_replace($matches[0], 'content-body', $html);

        $html = str_replace(' </a>', '</a> ', $html);

        $html = str_replace('<strong> </strong>', ' ', $html);

        $this->crawler = new DomCrawler((string) $html);

        $this->remove((array) $this->removables);
    }

    /**
     * Converts video elements to readable string.
     *
     * @param  \Pilipinews\Common\Crawler $crawler
     * @return \Pilipinews\Common\Crawler
     */
    protected function video(DomCrawler $crawler)
    {
        $callback = function (DomCrawler $crawler)
        {
            $embed = strpos($link = $crawler->attr('src'), 'embed');

            $type = $embed !== false ? 'EMBED' : 'VIDEO';

            return '<p>' . $type . ': ' . $link . '</p><br><br><br>';
        };

        return $this->replace($crawler, 'p > iframe', $callback);
    }
}
