<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$rules = [
    '@PER-CS' => true,
    '@PHP8x5Migration' => true,
    'align_multiline_comment' => [
        'comment_type' => 'phpdocs_only',
    ],
    'blank_line_before_statement' => [
        'statements' => [
            'break',
            'continue',
            'declare',
            'return',
            'throw',
            'try',
        ],
    ],
    'class_attributes_separation' => [
        'elements' => [
            'const' => 'one',
            'method' => 'one',
            'property' => 'one',
            'trait_import' => 'none',
            'case' => 'none',
        ],
    ],
    'class_reference_name_casing' => true,
    'combine_consecutive_issets' => true,
    'combine_consecutive_unsets' => true,
    'include' => true,
    'increment_style' => [
        'style' => 'post',
    ],
    'linebreak_after_opening_tag' => true,
    'magic_constant_casing' => true,
    'magic_method_casing' => true,
    'multiline_whitespace_before_semicolons' => [
        'strategy' => 'no_multi_line',
    ],
    'native_function_casing' => true,
    'native_type_declaration_casing' => true,
    'no_blank_lines_after_phpdoc' => true,
    'no_empty_phpdoc' => true,
    'no_empty_statement' => true,
    'no_extra_blank_lines' => [
        'tokens' => [
            'attribute',
            'break',
            'case',
            'continue',
            'curly_brace_block',
            'default',
            'extra',
            'parenthesis_brace_block',
            'return',
            'square_brace_block',
            'switch',
            'throw',
            'use',
        ],
    ],
    'no_leading_namespace_whitespace' => true,
    'no_mixed_echo_print' => [
        'use' => 'echo',
    ],
    'no_multiline_whitespace_around_double_arrow' => true,
    'no_short_bool_cast' => true,
    'no_singleline_whitespace_before_semicolons' => true,
    'no_spaces_around_offset' => [
        'positions' => [
            'inside',
            'outside',
        ],
    ],
    'no_unneeded_braces' => [
        'namespaces' => false,
    ],
    'no_unneeded_control_parentheses' => [
        'statements' => [
            'break',
            'clone',
            'continue',
            'echo_print',
            'return',
            'switch_case',
            'yield',
        ],
    ],
    'no_unused_imports' => true,
    'no_useless_else' => true,
    'no_useless_return' => true,
    'no_trailing_comma_in_singleline' => [
        'elements' => [
            'arguments',
            'array_destructuring',
            'array',
            'group_import',
        ],
    ],
    'nullable_type_declaration' => [
        'syntax' => 'question_mark',
    ],
    'nullable_type_declaration_for_default_null_value' => true,
    'object_operator_without_whitespace' => true,
    'ordered_interfaces' => [
        'case_sensitive' => false,
        'direction' => 'ascend',
        'order' => 'alpha',
    ],
    'ordered_types' => [
        'case_sensitive' => false,
        'null_adjustment' => 'always_first',
        'sort_algorithm' => 'alpha',
    ],
    'phpdoc_indent' => true,
    'phpdoc_inline_tag_normalizer' => [
        'tags' => [
            'example',
            'id',
            'internal',
            'inheritdoc',
            'inheritdocs',
            'link',
            'source',
            'toc',
            'tutorial',
        ],
    ],
    'phpdoc_no_access' => true,
    'phpdoc_no_package' => true,
    'phpdoc_no_useless_inheritdoc' => true,
    'phpdoc_scalar' => [
        'types' => [
            'boolean',
            'callback',
            'double',
            'integer',
            'real',
            'str',
        ],
    ],
    'phpdoc_single_line_var_spacing' => true,
    'phpdoc_trim' => true,
    'phpdoc_types' => [
        'groups' => [
            'simple',
            'alias',
            'meta',
        ],
    ],
    'phpdoc_types_order' => [
        'case_sensitive' => false,
        'null_adjustment' => 'always_first',
        'sort_algorithm' => 'alpha',
    ],
    'phpdoc_var_without_name' => true,
    'return_to_yield_from' => true,
    'single_line_comment_style' => [
        'comment_types' => [
            'asterisk',
            'hash',
        ],
    ],
    'single_quote' => [
        'strings_containing_single_quote_chars' => false,
    ],
    'space_after_semicolon' => [
        'remove_in_empty_for_expressions' => false,
    ],
    'standardize_not_equals' => true,
    'trim_array_spaces' => true,
    'type_declaration_spaces' => [
        'elements' => [
            'constant',
            'function',
            'property',
        ],
    ],
    'whitespace_after_comma_in_array' => [
        'ensure_single_space' => true,
    ],
];

$finder = Finder::create()
    ->in([
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return new Config()
    ->setFinder($finder)
    ->setRules($rules)
    ->setRiskyAllowed(false)
    ->setUsingCache(true);
