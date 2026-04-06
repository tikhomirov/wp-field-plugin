# WP_Field — Supported Matrix (Iteration 1)

_Обновлено: 2026-04-06_

## Область матрицы

Матрица фиксирует **legacy registry** из `WP_Field::init_field_types()`:
- **52 unique types**
- **4 aliases**

Статусы:
- `native` — рендер и поведение в OOP-классе `src/Field/Types/*`
- `bridge` — OOP-класс есть, но `render()` идёт через `LegacyAdapterBridge`/`LegacyWrapperField`
- `legacy-only` — в `Field::make()` нет отдельного OOP-класса, используется fallback `LegacyWrapperField`

Общие обязательные атрибуты для всех типов: `name`, `id`, `type`.

---

## 1) Матрица типов (52 unique)

| Type | OOP-класс | Статус | Обязательные атрибуты (сверх base) | Формат `value` | Required CSS/JS hooks | sanitize / validate |
|---|---|---|---|---|---|---|
| `text` | `TextField` | native | — | `string` | базовый input markup | `sanitize_text_field`, `required/min/max/pattern/email/url` из `AbstractField` |
| `password` | `InputField(type=password)` | native | — | `string` | базовый input markup | как у `AbstractField` |
| `email` | `InputField(type=email)` | native | — | `string` | базовый input markup | как у `AbstractField` + правило `email()` |
| `url` | `InputField(type=url)` | native | — | `string` | базовый input markup | как у `AbstractField` + правило `url()` |
| `tel` | `InputField(type=tel)` | native | — | `string` | базовый input markup | как у `AbstractField` |
| `number` | `InputField(type=number)` | native | `min/max/step` (опц.) | `string\|int\|float` | базовый input markup | как у `AbstractField` |
| `range` | `InputField(type=range)` | native | `min/max/step` (опц.) | `string\|int\|float` | базовый input markup | как у `AbstractField` |
| `hidden` | `InputField(type=hidden)` | native | — | `string` | базовый input markup | как у `AbstractField` |
| `textarea` | `TextareaField` | native | `rows` (опц.) | `string` | базовый textarea markup | как у `AbstractField` |
| `select` | `SelectField` | native | `options` | `scalar` | `select` markup | sanitize из `AbstractField`, validate из `AbstractField` |
| `multiselect` | `SelectField::multiple()` | native | `options` | `array<scalar>` | `select[multiple]` markup | sanitize рекурсивный из `AbstractField`, validate из `AbstractField` |
| `radio` | `RadioField` | bridge | `options` | `scalar` | legacy `render_radio` hooks | sanitize/validate от `AbstractField` (через wrapper) |
| `checkbox` | `CheckboxField` | native | `checkbox_value` (опц., default `1`) | `string` (`''`/`checkbox_value`) | `<input type="checkbox">` | custom sanitize: `''` или `checkbox_value`; validate из `AbstractField` |
| `checkbox_group` | `CheckboxGroupField` | native | `options` | `array<scalar>` | группа чекбоксов `name[]` | custom sanitize массива (`sanitize_text_field` по элементам); validate из `AbstractField` |
| `editor` | `EditorField` | bridge | `settings` (опц.) | `string` | legacy `render_editor` + wp_editor hooks | sanitize/validate от `AbstractField` |
| `media` | `MediaField` | bridge | `button_label`, `library` (опц.) | `string\|int` (id/url в legacy формате) | legacy `render_media` + media modal hooks | sanitize/validate от `AbstractField` |
| `image` | `ImageField` | bridge | `button_label` (опц.) | `string\|int` | legacy `render_image` + media hooks | sanitize/validate от `AbstractField` |
| `file` | `FileField` | bridge | `button_label` (опц.) | `string\|int` | legacy `render_file` + media hooks | sanitize/validate от `AbstractField` |
| `gallery` | `GalleryField` | bridge | `button_label` (опц.) | `array<int\|string>` | legacy `render_gallery` + media hooks | sanitize рекурсивный из `AbstractField`, validate из `AbstractField` |
| `color` | `ColorField` | bridge | `alpha` (опц.) | `string` (`#hex`/rgba) | legacy `render_color` + color picker hooks | sanitize/validate от `AbstractField` |
| `date` | `InputField(type=date)` | native | — | `string` | input date markup | sanitize/validate от `AbstractField` |
| `time` | `InputField(type=time)` | native | — | `string` | input time markup | sanitize/validate от `AbstractField` |
| `datetime` | `InputField(type=datetime-local)` (через normalizer) | native | — | `string` | input datetime-local markup | sanitize/validate от `AbstractField` |
| `group` | `GroupField` | native | `fields` | `array<string,mixed>` | `.wp-field-group*` | custom sanitize/validate по вложенным полям |
| `repeater` | `RepeaterField` | native | `fields`; `min/max` (опц.) | `array<int,array<string,mixed>>` | `.wp-field-repeater*` + шаблон `.wp-field-repeater-template` | custom sanitize/validate по sub-fields + min/max |
| `switcher` | `SwitcherField` | native | `text_on/text_off` (опц.) | `string` (`''`/`switcher_value`) | `.wp-field-switcher*` | sanitize/validate от `AbstractField` |
| `spinner` | `SpinnerField` | native | `min/max/step` (опц.) | `string\|int\|float` | `.wp-field-spinner*` | sanitize/validate от `AbstractField` |
| `button_set` | `ButtonSetField` | native | `options` | `scalar` | `.wp-field-button-set*` | sanitize/validate от `AbstractField` |
| `slider` | `SliderField` | native | `min/max/step` (опц.) | `string\|int\|float` | `.wp-field-slider` + `.wp-field-slider-value` | sanitize/validate от `AbstractField` |
| `heading` | `HeadingField` | native | `label`; `tag` (опц.) | `string` | статический heading markup | sanitize/validate от `AbstractField` |
| `subheading` | `SubheadingField` | native | `label`; `tag` (опц.) | `string` | статический subheading markup | sanitize/validate от `AbstractField` |
| `notice` | `NoticeField` | native | `label`; `notice_type` (опц.) | `string` | `.wp-field-notice*` | sanitize/validate от `AbstractField` |
| `content` | `ContentField` | native | `content` | `string` | raw HTML output | sanitize/validate от `AbstractField` |
| `fieldset` | `FieldsetField` | bridge | `fields` | `array<string,mixed>` | legacy `render_fieldset` | sanitize/validate от `AbstractField` |
| `accordion` | `AccordionField` | bridge | `sections/items` | `array` | legacy `render_accordion` + accordion JS hooks | sanitize/validate от `AbstractField` |
| `tabbed` | `TabbedField` | bridge | `tabs` | `array` | legacy `render_tabbed` + tabs JS hooks | sanitize/validate от `AbstractField` |
| `typography` | `TypographyField` | bridge | `default/options` (по подполям) | `array{family?,weight?,size?,line_height?,color?...}` | legacy `render_typography` hooks | sanitize/validate от `AbstractField` |
| `spacing` | `SpacingField` | bridge | `units`/`sides` (опц.) | `array{top?,right?,bottom?,left?,unit?}` | legacy `render_spacing` hooks | sanitize/validate от `AbstractField` |
| `dimensions` | `DimensionsField` | bridge | `units` (опц.) | `array{width?,height?,unit?}` | legacy `render_dimensions` hooks | sanitize/validate от `AbstractField` |
| `border` | `BorderField` | bridge | `styles` (опц.) | `array{style?,width?,color?}` | legacy `render_border` hooks | sanitize/validate от `AbstractField` |
| `background` | `BackgroundField` | bridge | `background_fields` (опц.) | `array{color?,image?,repeat?,position?,size?,attachment?}` | legacy `render_background` + media/color hooks | sanitize/validate от `AbstractField` |
| `link_color` | `LinkColorField` | bridge | `states` (опц.) | `array{regular?,hover?,active?,visited?}` | legacy `render_link_color` + color hooks | sanitize/validate от `AbstractField` |
| `color_group` | `ColorGroupField` | bridge | `options` | `array<string,string>` | legacy `render_color_group` + color hooks | sanitize/validate от `AbstractField` |
| `image_select` | `ImageSelectField` | native | `options` (value=>image URL/label) | `scalar` | `.wp-field-image-select*` | sanitize/validate от `AbstractField` |
| `code_editor` | `CodeEditorField` | bridge | `mode` (опц.) | `string` | legacy `render_code_editor` + CodeMirror hooks | sanitize/validate от `AbstractField` |
| `icon` | `IconField` | bridge | `library` (опц.) | `string` | legacy `render_icon` hooks | sanitize/validate от `AbstractField` |
| `map` | `MapField` | bridge | `zoom` (опц.) | `array\|string` (адрес/lat-lng по legacy) | legacy `render_map` + map hooks | sanitize/validate от `AbstractField` |
| `sortable` | `SortableField` | bridge | `options` | `array` | legacy `render_sortable` + sortable JS hooks | sanitize/validate от `AbstractField` |
| `sorter` | `SorterField` | bridge | `options`/`groups` | `array` | legacy `render_sorter` + sorter JS hooks | sanitize/validate от `AbstractField` |
| `palette` | `PaletteField` | bridge | `options` | `scalar` | legacy `render_palette` hooks | sanitize/validate от `AbstractField` |
| `link` | `LinkField` | bridge | — | `array{url?,title?,target?}` | legacy `render_link` + wpLink/media hooks | sanitize/validate от `AbstractField` |
| `backup` | `BackupField` | bridge | `backup` payload source | `mixed` | legacy `render_backup` hooks | sanitize/validate от `AbstractField` |

---

## 2) Alias map (4 aliases)

| Alias | Canonical type | OOP-класс/маршрут | Статус |
|---|---|---|---|
| `date_time` | `datetime` → `datetime-local` | `InputField(type=datetime-local)` | native |
| `datetime-local` | `datetime`/`datetime-local` | `InputField(type=datetime-local)` | native |
| `image_picker` | `image_picker` (legacy render_image_picker) | `ImagePickerField` | bridge |
| `imagepicker` | `image_picker` | `ImagePickerField` (через normalizer `imagepicker` → `image_picker`) | bridge |

---

## 3) Coverage summary

- Native: **27**
- Bridge: **25**
- Legacy-only: **0**
- Total unique: **52**
- Aliases: **4**

Проверка: `27 + 25 + 0 = 52`.

---

## 4) Примечания по контракту sanitize/validate

1. Базовый контракт (`AbstractField`):
   - `sanitize()` -> рекурсивно приводит к строкам + `sanitize_text_field`.
   - `validate()` -> `required` + правила из `HasValidation` (`min/max/email/url/pattern`).
2. Спец-обработчики:
   - `CheckboxField::sanitize()`
   - `CheckboxGroupField::sanitize()`
   - `RepeaterField::sanitize()/validate()`
   - `FlexibleContentField::sanitize()/validate()`
3. Для `bridge` и `legacy-only` в текущем runtime используется sanitize/validate OOP-объекта + legacy-render контракт.

---

## 5) Вне legacy registry

`flexible_content` поддерживается в modern API (`Field::flexibleContent`, `Field::make('flexible_content', ...)`) как `native`, но не входит в список 52 unique legacy-типа.
