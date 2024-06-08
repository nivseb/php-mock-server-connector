<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths(['./src', './tests']);
    $rectorConfig->skip(['./vendor']);

    $rectorConfig->importNames();
    $rectorConfig->removeUnusedImports();

    $rectorConfig->rule(Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector::class);
    $rectorConfig->rule(Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector::class);
    $rectorConfig->rule(Rector\CodeQuality\Rector\Assign\CombinedAssignRector::class);
    $rectorConfig->rule(Rector\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector::class);
    $rectorConfig->rule(Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector::class);
    $rectorConfig->rule(Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector::class);
    $rectorConfig->rule(Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector::class);
    $rectorConfig->rule(Rector\CodingStyle\Rector\Plus\UseIncrementAssignRector::class);
    $rectorConfig->rule(Rector\DeadCode\Rector\Cast\RecastingRemovalRector::class);
    $rectorConfig->rule(Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector::class);
    $rectorConfig->rule(Rector\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector::class);
    $rectorConfig->rule(Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector::class);
    $rectorConfig->rule(Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector::class);
    $rectorConfig->rule(Rector\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector::class);
    $rectorConfig->rule(Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector::class);
    $rectorConfig->rule(Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector::class);
    $rectorConfig->rule(Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector::class);
    $rectorConfig->rule(Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector::class);
    $rectorConfig->rule(Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector::class);
    $rectorConfig->rule(Rector\EarlyReturn\Rector\If_\ChangeNestedIfsToEarlyReturnRector::class);
    $rectorConfig->rule(Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector::class);
    $rectorConfig->rule(Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector::class);
    $rectorConfig->rule(Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector::class);
    $rectorConfig->rule(Rector\Php81\Rector\Property\ReadOnlyPropertyRector::class);
    $rectorConfig->rule(Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromPropertyTypeRector::class);
    $rectorConfig->rule(Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnDirectArrayRector::class);
    $rectorConfig->rule(Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector::class);
    $rectorConfig->rule(Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector::class);
    $rectorConfig->rule(Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictConstantReturnRector::class);
    $rectorConfig->rule(Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector::class);
};
