import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';

const RepeaterField = ({
  name,
  fields,
  value = [],
  min = 0,
  max = 0,
  buttonLabel = 'Add Row',
  layout = 'table',
}) => {
  const [rows, setRows] = useState(
    value.length > 0 ? value : min > 0 ? Array(min).fill({}) : []
  );

  const addRow = () => {
    if (max > 0 && rows.length >= max) return;
    setRows([...rows, {}]);
  };

  const removeRow = (index) => {
    if (min > 0 && rows.length <= min) return;
    setRows(rows.filter((_, i) => i !== index));
  };

  const updateRow = (index, fieldName, value) => {
    const newRows = [...rows];
    newRows[index] = { ...newRows[index], [fieldName]: value };
    setRows(newRows);
  };

  const renderField = (field, rowIndex, rowData) => {
    const fieldName = field.name;
    const fieldValue = rowData[fieldName] || '';
    const fullName = `${name}[${rowIndex}][${fieldName}]`;

    return (
      <div key={fieldName} className="wp-field-repeater-field">
        {field.label && <label>{field.label}</label>}
        <input
          type={field.type || 'text'}
          name={fullName}
          value={fieldValue}
          onChange={(e) => updateRow(rowIndex, fieldName, e.target.value)}
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

  const renderRow = (rowData, index) => {
    if (layout === 'table') {
      return (
        <tr key={index} className="wp-field-repeater-row" data-index={index}>
          {fields.map((field) => (
            <td key={field.name}>{renderField(field, index, rowData)}</td>
          ))}
          <td className="wp-field-repeater-actions">
            <button
              type="button"
              className="button wp-field-repeater-remove"
              onClick={() => removeRow(index)}
              disabled={min > 0 && rows.length <= min}
            >
              Remove
            </button>
          </td>
        </tr>
      );
    }

    return (
      <div key={index} className="wp-field-repeater-row" data-index={index}>
        {fields.map((field) => renderField(field, index, rowData))}
        <div className="wp-field-repeater-actions">
          <button
            type="button"
            className="button wp-field-repeater-remove"
            onClick={() => removeRow(index)}
            disabled={min > 0 && rows.length <= min}
          >
            Remove
          </button>
        </div>
      </div>
    );
  };

  return (
    <div className="wp-field-repeater" data-name={name} data-layout={layout}>
      <div className="wp-field-repeater-rows">
        {layout === 'table' ? (
          <table className="widefat">
            <tbody>{rows.map((row, index) => renderRow(row, index))}</tbody>
          </table>
        ) : (
          rows.map((row, index) => renderRow(row, index))
        )}
      </div>
      <button
        type="button"
        className="button wp-field-repeater-add"
        onClick={addRow}
        disabled={max > 0 && rows.length >= max}
      >
        {buttonLabel}
      </button>
    </div>
  );
};

window.WPFieldRepeater = RepeaterField;

if (typeof window !== 'undefined') {
  window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-wp-field-repeater]').forEach((el) => {
      const config = JSON.parse(el.dataset.wpFieldRepeater);
      const root = createRoot(el);
      root.render(<RepeaterField {...config} />);
    });
  });
}

export default RepeaterField;
