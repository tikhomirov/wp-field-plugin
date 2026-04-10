import React from 'react';

/**
 * Sticky header bar для AdminUI shell.
 *
 * @param {string} title
 * @param {{id: string, label: string}|undefined} activeSection
 */
export default function HeaderBar({ title, activeSection }) {
  return (
    <div className="iiko-admin-header" role="banner">
      <div className="iiko-admin-header__breadcrumb">
        <span className="iiko-admin-header__page">{title}</span>
        {activeSection && (
          <>
            <span className="iiko-admin-header__separator" aria-hidden="true">
              {' '}
              ›{' '}
            </span>
            <span className="iiko-admin-header__section">
              {activeSection.label}
            </span>
          </>
        )}
      </div>
    </div>
  );
}
