<?php

$finder = PhpCsFixer\Finder::create()
    ->ignoreDotFiles(true)
    ->ignoreVCSIgnored(true)
    ->in(['./src','./tests']);

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->registerCustomFixers(new PhpCsFixerCustomFixers\Fixers())
    ->setRiskyAllowed(true)
    ->setRules(
        [
            '@PHP82Migration'                                                         => true,
            '@PSR2'                                                                   => true,
            '@PSR12'                                                                  => true,
            '@PhpCsFixer'                                                             => true,
            '@Symfony:risky'                                                          => true,
            PhpCsFixerCustomFixers\Fixer\NoLeadingSlashInGlobalNamespaceFixer::name() => true,
            PhpCsFixerCustomFixers\Fixer\PhpdocNoSuperfluousParamFixer::name()        => true,
            PhpCsFixerCustomFixers\Fixer\CommentSurroundedBySpacesFixer::name()       => true,
            PhpCsFixerCustomFixers\Fixer\CommentedOutFunctionFixer::name()            => ['print_r', 'var_dump', 'var_export', 'dump', 'dd', 'ray'],
            PhpCsFixerCustomFixers\Fixer\ConstructorEmptyBracesFixer::name()          => true,
            PhpCsFixerCustomFixers\Fixer\MultilinePromotedPropertiesFixer::name()     => true,
            PhpCsFixerCustomFixers\Fixer\NoCommentedOutCodeFixer::name()              => true,
            PhpCsFixerCustomFixers\Fixer\NoDuplicatedArrayKeyFixer::name()            => true,
            PhpCsFixerCustomFixers\Fixer\NoPhpStormGeneratedCommentFixer::name()      => true,
            PhpCsFixerCustomFixers\Fixer\NoUselessCommentFixer::name()                => true,
            PhpCsFixerCustomFixers\Fixer\PhpdocArrayStyleFixer::name()                => true,
            PhpCsFixerCustomFixers\Fixer\PhpdocParamTypeFixer::name()                 => true,
            PhpCsFixerCustomFixers\Fixer\PhpdocSingleLineVarFixer::name()             => true,
            PhpCsFixerCustomFixers\Fixer\PhpdocTypesCommaSpacesFixer::name()          => true,
            PhpCsFixerCustomFixers\Fixer\PhpdocTypesTrimFixer::name()                 => true,
            PhpCsFixerCustomFixers\Fixer\PromotedConstructorPropertyFixer::name()     => true,
            PhpCsFixerCustomFixers\Fixer\StringableInterfaceFixer::name()             => true,
            'simplified_null_return'                                                  => false,
            'no_unused_imports'                                                       => true,
            'fully_qualified_strict_types'                                            => true,
            'align_multiline_comment'                                                 => true,
            'array_indentation'                                                       => true,
            'binary_operator_spaces'                                                  => [
                'default'   => 'single_space',
                'operators' => [
                    '=>' => 'align_single_space_minimal',
                    '='  => 'align_single_space_minimal',
                ],
            ],
            'modernize_types_casting'                => true,
            'modernize_strpos'                       => true,
            'get_class_to_class_keyword'             => true,
            'no_useless_else'                        => true,
            'simplified_if_return'                   => true,
            'ternary_to_elvis_operator'              => true,
            'statement_indentation'                  => true,
            'no_php4_constructor'                    => true,
            'no_superfluous_phpdoc_tags'             => true,
            'void_return'                            => true,
            'yoda_style'                             => false,
            'single_line_comment_style'              => ['comment_types' => ['hash']],
            'php_unit_test_class_requires_covers'    => false,
            'php_unit_internal_class'                => false,
            'return_assignment'                      => false,
            'octal_notation'                         => false,
            'non_printable_character'                => true,
            'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
            'global_namespace_import'                => ['import_classes' => true, 'import_constants' => false, 'import_functions' => false],
            'single_line_after_imports'              => true,
            'native_function_invocation'             => false,
            'self_accessor'                          => false,
            'no_unneeded_final_method'               => true,
            'no_unset_on_property'                   => true,
            'cast_spaces'                            => true,
        ]
    )
    ->setFinder($finder);
