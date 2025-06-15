<?php

declare(strict_types=1);

use Rumenx\PhpChatbot\Adapters\Symfony\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

it('Configuration returns a TreeBuilder with expected structure', function () {
    $config = new Configuration();
    $tree = $config->getConfigTreeBuilder();
    expect($tree)->toBeInstanceOf(TreeBuilder::class);
    $root = $tree->buildTree();
    expect($root->getName())->toBe('php_chatbot');
    $children = $root->getChildren();
    expect($children)->toHaveKey('api_key');
    expect($children)->toHaveKey('model');
});
