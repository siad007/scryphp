<?php
/**
 * Scryphp.
 * Batch Tool for scrayping websites using xml based execution plans.
 *
 * @author Siad Ardroumli <siad.ardroumli@idealo.de>
 * @package Scryphp
 * @version 0.1
 * @since 0.1
 */

namespace Scryphp;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Goutte\Client;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Execution Plans.
 */
class Plan implements ConfigurationInterface
{

    protected $name = '';

    protected $plan = null;

    protected $client = null;

    protected $selector = null;

    public function __construct($name)
    {
        $this->name = strtolower($name);

        try {
            $config = Yaml::parse(__DIR__."/../../plans/{$name}.yml");
        } catch (ParseException $pe) {
            throw new Exception('Cannot parse plan file.');
        }

        $processor = new Processor();
        $this->plan = $processor->processConfiguration($this, $config);

        $this->client = new Client();
    }

    public function setSelector($selector)
    {
        $this->selector = $selector;

        return $this;
    }

    public function getPlan()
    {
        return $this->plan;
    }

    public function setPlan($plan)
    {
        $this->plan = $plan;

        return $this;
    }

    public function process()
    {
        $crawler = $this->client->request(
            $this->plan['method'],
            $this->plan['uri']
        );

        if (isset($this->plan['selector'])) {
            $selection = $crawler->filter($this->plan['selector']);
        } elseif (isset($this->plan['xpath'])) {
            $selection = $crawler->filterXPath($this->plan['path']);
        }

        if ($this->plan['images']) {
            $images = $selection->filterXPath('//img');

            if (iterator_count($images) > 1) {
                foreach ($images as $image) {
                    $crawler = new Crawler($image);
                    $info = parse_url($this->plan['uri']);
                    $url = $info['scheme'] . '://' . $info['host'] . '/' . $crawler->attr('src');

                    if (strpos($crawler->attr('src'), 'http') === 0) {
                        $url = $info['scheme'] .'://' . $info['host'] . '/' . $this->plan['path'] . $crawler->attr('src');
                    }

                    copy($url, SCRYPHP_STORAGE_PATH_IMG . DIRECTORY_SEPARATOR . substr(strrchr($url, "/"), 1));
                }
            }
        }
        file_put_contents(
            SCRYPHP_STORAGE_PATH_TXT
            . DIRECTORY_SEPARATOR
            . time()
            . uniqid(time(), true)
            . '.txt',
            $selection->text()
        );

        return $selection->text();
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->name);
        $rootNode->children()
            ->scalarNode('uri')
            ->isRequired()
            ->cannotBeEmpty()
            ->end()
            ->scalarNode('selector')
            ->end()
            ->scalarNode('xpath')
            ->end()
            ->booleanNode('images')
            ->defaultValue(true)
            ->end()
            ->scalarNode('method')
            ->defaultValue('GET')
            ->end()
            ->end();

        return $treeBuilder;
    }
}
