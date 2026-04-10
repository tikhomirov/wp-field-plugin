import React from 'react';

const ALLOWED_TONES = ['neutral', 'success', 'warning', 'error'];

/**
 * Reusable inline alert for admin-shell surfaces.
 */
export default function Alert({
  tone = 'neutral',
  title = '',
  children = null,
  className = '',
  role,
}) {
  const resolvedTone = ALLOWED_TONES.includes(tone) ? tone : 'neutral';
  const resolvedRole = role || (resolvedTone === 'error' ? 'alert' : 'status');
  const classes = [
    'wp-field-alert',
    `wp-field-alert--${resolvedTone}`,
    className,
  ]
    .filter(Boolean)
    .join(' ');

  return (
    <div className={classes} role={resolvedRole}>
      {title && <div className="wp-field-alert__title">{title}</div>}
      {children && <div className="wp-field-alert__body">{children}</div>}
    </div>
  );
}
