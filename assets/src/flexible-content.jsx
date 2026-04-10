import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';

const FlexibleContentField = ({
  name,
  layouts,
  value = [],
  min = 0,
  max = 0,
  buttonLabel = 'Add Layout',
}) => {
  const [blocks, setBlocks] = useState(value);
  const [showLayoutPicker, setShowLayoutPicker] = useState(false);

  const addBlock = (layoutName) => {
    if (max > 0 && blocks.length >= max) return;
    setBlocks([...blocks, { acf_fc_layout: layoutName }]);
    setShowLayoutPicker(false);
  };

  const removeBlock = (index) => {
    if (min > 0 && blocks.length <= min) return;
    setBlocks(blocks.filter((_, i) => i !== index));
  };

  const updateBlock = (index, fieldName, value) => {
    const newBlocks = [...blocks];
    newBlocks[index] = { ...newBlocks[index], [fieldName]: value };
    setBlocks(newBlocks);
  };

  const toggleBlock = (index) => {
    const block = document.querySelector(`[data-block-index="${index}"]`);
    if (block) {
      block.classList.toggle('collapsed');
    }
  };

  const renderField = (field, blockIndex, blockData) => {
    const fieldName = field.name;
    const fieldValue = blockData[fieldName] || '';
    const fullName = `${name}[${blockIndex}][${fieldName}]`;

    return (
      <div key={fieldName} className="wp-field-flexible-field">
        {field.label && <label>{field.label}</label>}
        <input
          type={field.type || 'text'}
          name={fullName}
          value={fieldValue}
          onChange={(e) => updateBlock(blockIndex, fieldName, e.target.value)}
          placeholder={field.placeholder || ''}
          required={field.required || false}
          className={field.class || ''}
        />
        {field.description && (
          <p className="description">{field.description}</p>
        )}
      </div>
    );
  };

  const renderBlock = (blockData, index) => {
    const layoutName = blockData.acf_fc_layout;
    const layout = layouts[layoutName];

    if (!layout) return null;

    return (
      <div
        key={index}
        className="wp-field-flexible-block"
        data-block-index={index}
        data-layout={layoutName}
      >
        <div className="wp-field-flexible-block-header">
          <h4>{layout.label}</h4>
          <div className="wp-field-flexible-block-controls">
            <button
              type="button"
              className="button wp-field-flexible-collapse"
              onClick={() => toggleBlock(index)}
            >
              −
            </button>
            <button
              type="button"
              className="button wp-field-flexible-remove"
              onClick={() => removeBlock(index)}
              disabled={min > 0 && blocks.length <= min}
            >
              Remove
            </button>
          </div>
        </div>
        <div className="wp-field-flexible-block-content">
          <input
            type="hidden"
            name={`${name}[${index}][acf_fc_layout]`}
            value={layoutName}
          />
          {layout.fields.map((field) => renderField(field, index, blockData))}
        </div>
      </div>
    );
  };

  return (
    <div className="wp-field-flexible" data-name={name}>
      <div className="wp-field-flexible-blocks">
        {blocks.map((block, index) => renderBlock(block, index))}
      </div>
      <div className="wp-field-flexible-add-block">
        <button
          type="button"
          className="button"
          onClick={() => setShowLayoutPicker(!showLayoutPicker)}
          disabled={max > 0 && blocks.length >= max}
        >
          {buttonLabel}
        </button>
        {showLayoutPicker && (
          <div className="wp-field-flexible-layouts">
            {Object.entries(layouts).map(([layoutName, layout]) => (
              <button
                key={layoutName}
                type="button"
                className="button"
                onClick={() => addBlock(layoutName)}
              >
                {layout.label}
              </button>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

window.WPFieldFlexibleContent = FlexibleContentField;

if (typeof window !== 'undefined') {
  window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-wp-field-flexible]').forEach((el) => {
      const config = JSON.parse(el.dataset.wpFieldFlexible);
      const root = createRoot(el);
      root.render(<FlexibleContentField {...config} />);
    });
  });
}

export default FlexibleContentField;
