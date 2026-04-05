import React, { useState } from 'react';

/**
 * Sidebar navigation for AdminShell.
 *
 * Supports a 2-level tree: groups (with children) and leaves.
 * Groups can be collapsed/expanded. The group containing the active segment
 * is always expanded initially.
 *
 * @param {Array}    nav             NavItem tree from PHP (JSON-parsed).
 * @param {string}   activeSegment   Active leaf id.
 * @param {Function} onSegmentChange Called with new leaf id on click.
 * @param {string}   title           Page title / sidebar logo text.
 */
export default function SidebarNav({ nav, activeSegment, onSegmentChange, title }) {
    // Track which group ids are open; start with the group that has the active child.
    const [openGroups, setOpenGroups] = useState(() => {
        const open = new Set();
        for (const item of nav) {
            if (item.children?.some((c) => c.id === activeSegment)) {
                open.add(item.id);
            }
        }
        return open;
    });

    const toggleGroup = (id) => {
        setOpenGroups((prev) => {
            const next = new Set(prev);
            if (next.has(id)) {
                next.delete(id);
            } else {
                next.add(id);
            }
            return next;
        });
    };

    return (
        <aside className="wp-field-shell__sidebar">
            {title && (
                <div className="wp-field-shell__logo">
                    <span className="wp-field-shell__logo-text">{title}</span>
                </div>
            )}

            <nav className="wp-field-shell__nav" aria-label="Settings navigation">
                {nav.map((item) => {
                    if (item.children?.length) {
                        return (
                            <NavGroup
                                key={item.id}
                                item={item}
                                activeSegment={activeSegment}
                                isOpen={openGroups.has(item.id)}
                                onToggle={() => toggleGroup(item.id)}
                                onSegmentChange={onSegmentChange}
                            />
                        );
                    }
                    return (
                        <NavLeaf
                            key={item.id}
                            item={item}
                            active={activeSegment === item.id}
                            onSegmentChange={onSegmentChange}
                        />
                    );
                })}
            </nav>
        </aside>
    );
}

function NavGroup({ item, activeSegment, isOpen, onToggle, onSegmentChange }) {
    const hasActiveChild = item.children?.some((c) => c.id === activeSegment);

    return (
        <>
            <span
                className={`wp-field-shell__nav-group wp-field-shell__nav-group--collapsible${hasActiveChild ? ' wp-field-shell__nav-group--has-active' : ''}`}
                role="button"
                tabIndex={0}
                aria-expanded={isOpen}
                onClick={onToggle}
                onKeyDown={(e) => e.key === 'Enter' || e.key === ' ' ? onToggle() : null}
            >
                {item.label}
                <span className={`wp-field-shell__nav-group-arrow${isOpen ? ' wp-field-shell__nav-group-arrow--open' : ''}`} aria-hidden="true">▶</span>
            </span>

            {isOpen && item.children?.map((child) => (
                <NavLeaf
                    key={child.id}
                    item={child}
                    active={activeSegment === child.id}
                    onSegmentChange={onSegmentChange}
                    isChild
                />
            ))}
        </>
    );
}

function NavLeaf({ item, active, onSegmentChange, isChild = false }) {
    const classes = [
        'wp-field-shell__nav-item',
        isChild ? 'wp-field-shell__nav-item--child' : '',
        active ? 'wp-field-shell__nav-item--active' : '',
    ].filter(Boolean).join(' ');

    return (
        <a
            href={`#${item.id}`}
            className={classes}
            aria-current={active ? 'page' : undefined}
            onClick={(e) => {
                e.preventDefault();
                onSegmentChange(item.id);
            }}
        >
            {item.label}
        </a>
    );
}
