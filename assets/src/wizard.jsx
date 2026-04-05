import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import WizardProgress from './components/WizardProgress.jsx';

/**
 * WP Field Wizard React app.
 *
 * Manages:
 *  - Active step state.
 *  - Visibility of PHP-rendered .wp-field-wizard-step elements.
 *  - Hidden input #wp-field-wizard-active-step.
 *  - URL query param (replaceState, key name from data attribute).
 *  - Optional host validation hook: window.wpFieldWizardHooks.beforeStep(fromId, toId) → bool.
 */
function WizardApp({ steps, initialStep, stepKey, labels }) {
    const stepIds = steps.map((s) => s.id);
    const validInitial = stepIds.includes(initialStep) ? initialStep : (stepIds[0] ?? '');

    const [activeStep, setActiveStep] = useState(validInitial);

    const activeIdx = stepIds.indexOf(activeStep);
    const isFirst   = activeIdx === 0;
    const isLast    = activeIdx === stepIds.length - 1;

    const navigate = (targetId) => {
        // Optional host hook: return false to block navigation
        const hooks = window.wpFieldWizardHooks;
        if (hooks?.beforeStep && hooks.beforeStep(activeStep, targetId) === false) {
            return;
        }
        setActiveStep(targetId);
    };

    const goNext = () => {
        if (!isLast) navigate(stepIds[activeIdx + 1]);
    };

    const goBack = () => {
        if (!isFirst) navigate(stepIds[activeIdx - 1]);
    };

    useEffect(() => {
        // Sync hidden input
        const input = document.getElementById('wp-field-wizard-active-step');
        if (input) input.value = activeStep;

        // Toggle step panel visibility
        document.querySelectorAll('.wp-field-wizard-step').forEach((el) => {
            el.style.display = el.getAttribute('data-wizard-step') === activeStep ? '' : 'none';
        });

        // Sync URL
        try {
            const url = new URL(window.location.href);
            if (activeStep === '' || activeStep === stepIds[0]) {
                url.searchParams.delete(stepKey);
            } else {
                url.searchParams.set(stepKey, activeStep);
            }
            window.history.replaceState({}, '', url.toString());
        } catch (_) {
            // Ignore URL errors
        }
    }, [activeStep]);

    return (
        <>
            <WizardProgress steps={steps} activeStep={activeStep} />

            <div className="wp-field-wizard__nav">
                <div className="wp-field-wizard__nav-left">
                    {!isFirst && (
                        <button type="button" className="button" onClick={goBack}>
                            {labels.back}
                        </button>
                    )}
                </div>

                <div className="wp-field-wizard__nav-right">
                    {!isLast ? (
                        <button type="button" className="button button-primary" onClick={goNext}>
                            {labels.next}
                        </button>
                    ) : (
                        <button
                            type="submit"
                            form="wp-field-wizard-form"
                            className="button button-primary"
                        >
                            {labels.finish}
                        </button>
                    )}
                </div>
            </div>
        </>
    );
}

function mountWizard() {
    const root = document.getElementById('wp-field-wizard-root');
    if (!root) return;

    let steps = [];
    try {
        steps = JSON.parse(root.getAttribute('data-steps') || '[]');
    } catch (e) {
        // eslint-disable-next-line no-console
        console.error('[wp-field-wizard] Failed to parse steps:', e);
    }

    const initialStep = root.getAttribute('data-initial-step') || '';
    const stepKey     = root.getAttribute('data-step-key') || 'wizard_step';
    const labels = {
        next:   root.getAttribute('data-next')   || 'Next',
        back:   root.getAttribute('data-back')   || 'Back',
        skip:   root.getAttribute('data-skip')   || 'Skip',
        finish: root.getAttribute('data-finish') || 'Finish',
    };

    createRoot(root).render(
        <WizardApp
            steps={steps}
            initialStep={initialStep}
            stepKey={stepKey}
            labels={labels}
        />,
    );
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountWizard);
} else {
    mountWizard();
}
