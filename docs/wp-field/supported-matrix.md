# WP_Field — Supported Matrix (финальный audit Stage 5)

_Обновлено: 2026-04-06_

## Область матрицы

Матрица фиксирует **legacy registry** из `WP_Field::init_field_types()`:
- **52 unique types**
- **4 aliases**

Статусы:
- `native` — рендер и поведение в OOP-классе `src/Field/Types/*`
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
| `radio` | `RadioField` | native | `options` | `scalar` | `.wp-field-radio-group` markup | sanitize/validate от `AbstractField` |
| `checkbox` | `CheckboxField` | native | `checkbox_value` (опц., default `1`) | `string` (`''`/`checkbox_value`) | `<input type="checkbox">` | custom sanitize: `''` или `checkbox_value`; validate из `AbstractField` |
| `checkbox_group` | `CheckboxGroupField` | native | `options` | `array<scalar>` | группа чекбоксов `name[]` | custom sanitize массива (`sanitize_text_field` по элементам); validate из `AbstractField` |
| `editor` | `EditorField` | native | `rows`, `media_buttons`, `teeny`, `wpautop` (опц.) | `string` | `.wp-editor-area` + optional `wp.editor` enhancement | sanitize/validate от `AbstractField` |
| `media` | `MediaField` | native | `button_text`, `library`, `preview` (опц.) | `string\|int` | `.wp-field-media-*` + optional `wp.media` enhancement | sanitize/validate от `AbstractField` |
| `image` | `ImageField` | native | `button_text`, `remove_text`, `preview` (опц.) | `string\|int` | `.wp-field-image-*` + optional `wp.media` enhancement | sanitize/validate от `AbstractField` |
| `file` | `FileField` | native | `button_text`, `library` (опц.) | `string\|int` | `.wp-field-file-*` + optional `wp.media` enhancement | sanitize/validate от `AbstractField` |
| `gallery` | `GalleryField` | native | `add_button`, `edit_button`, `clear_button` (опц.) | `array<int\|string>\|csv-string` | `.wp-field-gallery-*` + optional `wp.media` enhancement | custom sanitize (csv id list), validate baseline |
| `color` | `ColorField` | native | `alpha` (опц.) | `string` (`#hex`/rgba) | `.wp-color-picker-field` + optional color picker enhancement | sanitize/validate от `AbstractField` |
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
| `fieldset` | `FieldsetField` | native | `fields` | `array<string,mixed>` | `.wp-field-fieldset*` markup | sanitize/validate от `AbstractField` |
| `accordion` | `AccordionField` | native | `sections/items` | `array` | `.wp-field-accordion*` server-render baseline (без обязательного JS) | sanitize/validate от `AbstractField` |
| `tabbed` | `TabbedField` | native | `tabs` | `array` | `.wp-field-tabbed*` + `role=tablist/tabpanel` baseline | sanitize/validate от `AbstractField` |
| `typography` | `TypographyField` | native | `default/options` (по подполям) | `array{font_family?,font_size?,font_weight?,line_height?,text_align?,text_transform?,color?}` | `.wp-field-typography` server-render baseline | custom sanitize/validate по sub-keys |
| `spacing` | `SpacingField` | native | `units`/`sides` (опц.) | `array{top?,right?,bottom?,left?,unit?}` | `.wp-field-spacing` server-render baseline | custom sanitize/validate по sub-keys |
| `dimensions` | `DimensionsField` | native | `units` (опц.) | `array{width?,height?,unit?}` | `.wp-field-dimensions` server-render baseline | custom sanitize/validate по sub-keys |
| `border` | `BorderField` | native | `styles` (опц.) | `array{style?,width?,color?}` | `.wp-field-border` server-render baseline | custom sanitize/validate по sub-keys |
| `background` | `BackgroundField` | native | `background_fields` (опц.) | `array{color?,image?,repeat?,position?,size?,attachment?}` | `.wp-field-background-*` + optional media/color enhancement | custom sanitize/validate по sub-keys |
| `link_color` | `LinkColorField` | native | `states` (опц.) | `array{normal?,hover?,active?,...custom_states}` | `.wp-field-link-color` + optional color picker enhancement | custom sanitize/validate по состояниям |
| `color_group` | `ColorGroupField` | native | `options`/`colors` | `array<string,string>` | `.wp-field-color-group*` + `wp-color-picker-field` inputs | custom sanitize массива + validate от `AbstractField` |
| `image_select` | `ImageSelectField` | native | `options` (value=>image URL/label) | `scalar` | `.wp-field-image-select*` | sanitize/validate от `AbstractField` |
| `code_editor` | `CodeEditorField` | native | `mode` (опц.) | `string` | `.wp-field-code-editor` + optional `wp.codeEditor` enhancement | sanitize/validate от `AbstractField` |
| `icon` | `IconField` | native | `library` (опц.) | `string` | `.wp-field-icon-*` + optional icon-picker enhancement | sanitize/validate от `AbstractField` |
| `map` | `MapField` | native | `zoom`, `center`, `api_key` (опц.) | `array{lat?: string, lng?: string}` | `.wp-field-map-*` baseline + optional geolocation/provider enhancement | custom sanitize/validate для lat/lng диапазонов |
| `sortable` | `SortableField` | native | `options` | `array` | `.wp-field-sortable` server-render list + hidden inputs | custom sanitize списка + validate от `AbstractField` |
| `sorter` | `SorterField` | native | `options`/`groups` | `array` | `.wp-field-sorter*` server-render columns + hidden inputs | custom sanitize grouped-list + validate от `AbstractField` |
| `palette` | `PaletteField` | native | `options`/`palettes` | `scalar` | `.wp-field-palette*` radio-card markup | sanitize/validate от `AbstractField` |
| `link` | `LinkField` | native | — | `array{url?,text?,target?}` | `.wp-field-link*` server-render inputs | custom sanitize shape + validate от `AbstractField` |
| `backup` | `BackupField` | native | `export_data` (опц.) | `string` (import JSON) | `.wp-field-backup*` server-render import/export UI | custom sanitize/validate JSON payload |

---

## 2) Alias map (4 aliases)

| Alias | Canonical type | OOP-класс/маршрут | Статус |
|---|---|---|---|
| `date_time` | `datetime` → `datetime-local` | `InputField(type=datetime-local)` | native |
| `datetime-local` | `datetime`/`datetime-local` | `InputField(type=datetime-local)` | native |
| `image_picker` | `image_picker` (normalizer target) | `ImagePickerField` | native |
| `imagepicker` | `image_picker` | `ImagePickerField` (через normalizer `imagepicker` → `image_picker`) | native |

---

## 3) Coverage summary (финал Stage 5)

- Native: **52**
- Legacy-only: **0**
- Total unique: **52**
- Aliases: **4**

Проверка: `52 + 0 = 52`.

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
3. `LegacyWrapperField` используется только для неизвестных/custom типов вне официального registry.

---

## 5) Вне legacy registry

`flexible_content` поддерживается в modern API (`Field::flexibleContent`, `Field::make('flexible_content', ...)`) как `native`, но не входит в список 52 unique legacy-типа.

---

## 6) Финальный audit: official registry route table

| Route | Статус |
|---|---|
| `text,password,email,url,tel,number,range,hidden,textarea,select,multiselect,radio,checkbox,checkbox_group` | native |
| `editor,media,image,file,gallery,color,date,time,datetime` | native |
| `group,repeater,switcher,spinner,button_set,slider,heading,subheading,notice,content,fieldset` | native |
| `accordion,tabbed,typography,spacing,dimensions,border,background,link_color,color_group,image_select` | native |
| `code_editor,icon,map,sortable,sorter,palette,link,backup` | native |
| alias `image_picker` / `imagepicker` | native (`ImagePickerField`) |
| alias `date_time` / `datetime-local` | native (`InputField(type=datetime-local)`) |
| unknown/custom type (например `my_custom_type`) | `legacy-only` fallback (`LegacyWrapperField`) |
