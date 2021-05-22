<?php declare(strict_types = 1);

namespace Nextras\Web\Docs;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Normalizer\TextNormalizerInterface;
use Nette\Utils\Strings;


class CommonMarkFactory
{
	function create(): MarkdownConverter
	{
		$environment = new Environment(
			[
				'heading_permalink' => [
					'min_heading_level' => 3,
					'html_class' => 'anchor',
					'insert' => 'after',
					'title' => 'Permalink',
					'symbol' => '#',
					'slug_normalizer' => new class implements TextNormalizerInterface {
						public function normalize(string $text, $context = null): string
						{
							return 'toc-' . Strings::webalize($text);
						}
					},
				],
			]
		);
		$environment->addExtension(new CommonMarkCoreExtension());
		$environment->addExtension(new HeadingPermalinkExtension());
		$environment->addExtension(new GithubFlavoredMarkdownExtension());
		$environment->addExtension(new SmartPunctExtension());
		$environment->addRenderer(FencedCode::class, new FencedCodeRenderer());
		return new MarkdownConverter($environment);
	}
}
