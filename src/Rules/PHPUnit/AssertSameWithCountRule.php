<?php declare(strict_types = 1);

namespace PHPStan\Rules\PHPUnit;

use PhpParser\Node;
use PHPStan\Analyser\Scope;

/**
 * @implements \PHPStan\Rules\Rule<\PhpParser\NodeAbstract>
 */
class AssertSameWithCountRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return \PhpParser\NodeAbstract::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if (!AssertRuleHelper::isMethodOrStaticCallOnAssert($node, $scope)) {
			return [];
		}

		/** @var \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node */
		$node = $node;

		if (count($node->args) < 2) {
			return [];
		}
		if (!$node->name instanceof Node\Identifier || strtolower($node->name->name) !== 'assertsame') {
			return [];
		}

		$right = $node->args[1]->value;

		if (
			$right instanceof Node\Expr\FuncCall
			&& $right->name instanceof Node\Name
			&& strtolower($right->name->toString()) === 'count'
		) {
			return [
				'You should use assertCount($expectedCount, $variable) instead of assertSame($expectedCount, count($variable)).',
			];
		}

		return [];
	}

}
