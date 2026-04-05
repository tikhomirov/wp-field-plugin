import React from 'react';

/**
 * Step progress indicator for the Wizard.
 *
 * @param {Array<{id: string, title: string}>} steps
 * @param {string}   activeStep   Id of the active step.
 */
export default function WizardProgress({ steps, activeStep }) {
    const activeIdx = steps.findIndex((s) => s.id === activeStep);

    return (
        <div className="wp-field-wizard__progress" role="list" aria-label="Wizard steps">
            {steps.map((step, idx) => {
                const isDone   = idx < activeIdx;
                const isActive = idx === activeIdx;
                const statusClass = isDone ? 'wp-field-wizard__step--done' : isActive ? 'wp-field-wizard__step--active' : '';

                return (
                    <React.Fragment key={step.id}>
                        {idx > 0 && (
                            <div className={`wp-field-wizard__step-connector${isDone ? ' wp-field-wizard__step-connector--done' : ''}`} aria-hidden="true" />
                        )}
                        <div
                            className={`wp-field-wizard__step ${statusClass}`.trim()}
                            role="listitem"
                            aria-current={isActive ? 'step' : undefined}
                        >
                            <div className="wp-field-wizard__step-bubble" aria-hidden="true">
                                {isDone ? '✓' : idx + 1}
                            </div>
                            <span className="wp-field-wizard__step-label">{step.title}</span>
                        </div>
                    </React.Fragment>
                );
            })}
        </div>
    );
}
