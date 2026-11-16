<?php

declare(strict_types=1);

namespace WpField\UI;

/**
 * Navigation item for AdminShell.
 *
 * Depth limit v1: 2 levels (group → leaves). Groups have children, no content.
 * Leaves have optional panels (tabs); content rendered via panel_renderer.
 *
 * Usage:
 *   NavItem::leaf('general', 'General')
 *   NavItem::leaf('api', 'API', [['id' => 'settings', 'label' => 'Settings'], ['id' => 'logs', 'label' => 'Logs']])
 *   NavItem::group('advanced', 'Advanced', [NavItem::leaf('cache', 'Cache')])
 *   NavItem::flatSections(['general' => 'General', 'api' => 'API'])
 */
final class NavItem
{
    /** @param array<int, array{id: string, label: string}>|null $panels */
    private function __construct(
        public readonly string $id,
        public readonly string $label,
        /** @var array<int, NavItem>|null */
        public readonly ?array $children,
        /** @var array<int, array{id: string, label: string}>|null */
        public readonly ?array $panels,
    ) {}

    /** Leaf node — renders content via panel_renderer.
     *
     * @param  array<int, array{id: string, label: string}>|null  $panels
     */
    public static function leaf(string $id, string $label, ?array $panels = null): self
    {
        return new self($id, $label, null, $panels ?: null);
    }

    /** Group node — wraps children, no content of its own.
     *
     * @param  array<int, NavItem>  $children
     */
    public static function group(string $id, string $label, array $children): self
    {
        return new self($id, $label, $children, null);
    }

    public function isGroup(): bool
    {
        return $this->children !== null && $this->children !== [];
    }

    public function isLeaf(): bool
    {
        return ! $this->isGroup();
    }

    /**
     * Build a flat depth-1 nav tree from a simple id=>label map.
     * Backward-compatible with current flat sections pattern.
     *
     * @param  array<string, string>  $sections
     * @return NavItem[]
     */
    public static function flatSections(array $sections): array
    {
        $items = [];
        foreach ($sections as $id => $label) {
            $items[] = self::leaf((string) $id, (string) $label);
        }

        return $items;
    }

    /**
     * Collect all leaves (depth-first) from a nav tree.
     *
     * @param  NavItem[]  $items
     * @return NavItem[]
     */
    public static function collectLeaves(array $items): array
    {
        $leaves = [];
        foreach ($items as $item) {
            if ($item->isGroup()) {
                foreach ($item->children ?? [] as $child) {
                    $leaves[] = $child;
                }
            } else {
                $leaves[] = $item;
            }
        }

        return $leaves;
    }

    /**
     * Find the first leaf id in the nav tree.
     *
     * @param  NavItem[]  $items
     */
    public static function firstLeafId(array $items): string
    {
        foreach (self::collectLeaves($items) as $leaf) {
            return $leaf->id;
        }

        return '';
    }

    /**
     * Find a leaf by id across the entire tree.
     *
     * @param  NavItem[]  $items
     */
    public static function findLeaf(array $items, string $id): ?self
    {
        foreach (self::collectLeaves($items) as $leaf) {
            if ($leaf->id === $id) {
                return $leaf;
            }
        }

        return null;
    }

    /**
     * Serialize the tree to a JSON-safe array for the React data attribute.
     *
     * @param  NavItem[]  $items
     * @return array<int, mixed>
     */
    public static function toJsonArray(array $items): array
    {
        $out = [];
        foreach ($items as $item) {
            $node = ['id' => $item->id, 'label' => $item->label];
            if ($item->isGroup()) {
                $node['children'] = self::toJsonArray($item->children ?? []);
            }
            if ($item->panels !== null) {
                $node['panels'] = $item->panels;
            }
            $out[] = $node;
        }

        return $out;
    }
}
