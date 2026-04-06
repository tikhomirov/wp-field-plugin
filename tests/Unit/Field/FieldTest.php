<?php

declare(strict_types=1);

use WpField\Field\Field;
use WpField\Field\Types\AccordionField;
use WpField\Field\Types\BackgroundField;
use WpField\Field\Types\BackupField;
use WpField\Field\Types\BorderField;
use WpField\Field\Types\ButtonSetField;
use WpField\Field\Types\CodeEditorField;
use WpField\Field\Types\ColorGroupField;
use WpField\Field\Types\ContentField;
use WpField\Field\Types\DimensionsField;
use WpField\Field\Types\GroupField;
use WpField\Field\Types\HeadingField;
use WpField\Field\Types\IconField;
use WpField\Field\Types\ImagePickerField;
use WpField\Field\Types\ImageSelectField;
use WpField\Field\Types\LinkColorField;
use WpField\Field\Types\LinkField;
use WpField\Field\Types\MapField;
use WpField\Field\Types\NoticeField;
use WpField\Field\Types\PaletteField;
use WpField\Field\Types\SliderField;
use WpField\Field\Types\SortableField;
use WpField\Field\Types\SorterField;
use WpField\Field\Types\SpacingField;
use WpField\Field\Types\SpinnerField;
use WpField\Field\Types\SubheadingField;
use WpField\Field\Types\SwitcherField;
use WpField\Field\Types\TabbedField;
use WpField\Field\Types\TypographyField;

it('Field::text creates a TextField with correct properties', function (): void {
    $field = Field::text('name')
        ->required()
        ->label('Имя');

    expect($field->toArray())
        ->toMatchArray([
            'type' => 'text',
            'required' => true,
            'label' => 'Имя',
        ]);
});

it('Field can chain multiple attributes', function (): void {
    $field = Field::text('email')
        ->label('Email')
        ->placeholder('example@example.com')
        ->required()
        ->email()
        ->class('form-control');

    $array = $field->toArray();

    expect($array)
        ->toHaveKey('type', 'text')
        ->toHaveKey('label', 'Email')
        ->toHaveKey('placeholder', 'example@example.com')
        ->toHaveKey('required', true)
        ->toHaveKey('class', 'form-control');
});

it('Field validates required values correctly', function (): void {
    $field = Field::text('name')->required();

    expect($field->validate(''))->toBeFalse()
        ->and($field->validate('John'))->toBeTrue();
});

it('Field validates email correctly', function (): void {
    $field = Field::text('email')->email();

    expect($field->validate('invalid-email'))->toBeFalse()
        ->and($field->validate('valid@example.com'))->toBeTrue();
});

it('Field validates min/max correctly', function (): void {
    $field = Field::text('age')->min(18)->max(100);

    expect($field->validate(17))->toBeFalse()
        ->and($field->validate(18))->toBeTrue()
        ->and($field->validate(50))->toBeTrue()
        ->and($field->validate(100))->toBeTrue()
        ->and($field->validate(101))->toBeFalse();
});

it('Field renders HTML correctly', function (): void {
    $field = Field::text('username')
        ->label('Username')
        ->placeholder('Enter username')
        ->required();

    $html = $field->render();

    expect($html)
        ->toContain('type="text"')
        ->toContain('name="username"')
        ->toContain('placeholder="Enter username"')
        ->toContain('required')
        ->toContain('<label');
});

it('Field sanitizes values', function (): void {
    $field = Field::text('name');

    $sanitized = $field->sanitize('<script>alert("xss")</script>');

    expect($sanitized)->not->toContain('<script>');
});

it('Field supports default values', function (): void {
    $field = Field::text('country')->default('USA');

    expect($field->getValue())->toBe('USA');
});

it('Field supports conditional logic', function (): void {
    $field = Field::text('city')
        ->when('country', '==', 'USA')
        ->orWhen('country', '==', 'Canada');

    $conditions = $field->getConditions();

    expect($conditions)
        ->toHaveCount(2)
        ->and($conditions[0])->toMatchArray([
            'field' => 'country',
            'operator' => '==',
            'value' => 'USA',
            'logic' => 'AND',
        ])
        ->and($conditions[1])->toMatchArray([
            'field' => 'country',
            'operator' => '==',
            'value' => 'Canada',
            'logic' => 'OR',
        ]);
});

it('Field::make creates native SelectField for select types', function (): void {
    $field = Field::make('select', 'status')
        ->label('Status')
        ->attribute('options', ['new' => 'New', 'done' => 'Done'])
        ->value('done');

    $html = $field->render();

    expect($field->getType())->toBe('select')
        ->and($html)->toContain('<select')
        ->and($html)->toContain('value="done" selected');
});

it('Field::make creates native TextareaField', function (): void {
    $field = Field::make('textarea', 'notes')
        ->label('Notes')
        ->value('hello');

    $html = $field->render();

    expect($field->getType())->toBe('textarea')
        ->and($html)->toContain('<textarea')
        ->and($html)->toContain('hello');
});

it('Field::make creates native CheckboxGroupField', function (): void {
    $field = Field::make('checkbox_group', 'tags')
        ->attribute('options', ['a' => 'Tag A', 'b' => 'Tag B'])
        ->value(['a']);

    $html = $field->render();

    expect($field->getType())->toBe('checkbox_group')
        ->and($html)->toContain('name="tags[]"')
        ->and($html)->toContain('Tag A')
        ->and($html)->toContain('checked');
});

it('Field::make maps editor media image file gallery to native classes', function (): void {
    $editor = Field::make('editor', 'content');
    $color = Field::make('color', 'accent_color');
    $image = Field::make('image', 'hero_image');
    $file = Field::make('file', 'brochure');
    $gallery = Field::make('gallery', 'photos');

    expect($editor->getType())->toBe('editor')
        ->and($color->getType())->toBe('color')
        ->and($image->getType())->toBe('image')
        ->and($file->getType())->toBe('file')
        ->and($gallery->getType())->toBe('gallery');
});

it('Field::make maps B1 structural types to native classes', function (): void {
    $group = Field::group('address')
        ->label('Address')
        ->fields([
            Field::text('city'),
            Field::text('street'),
        ])
        ->value([
            'city' => 'Москва',
            'street' => 'Тверская',
        ]);

    $heading = Field::heading('section_title')
        ->label('Main Section')
        ->tag('h2');

    $subheading = Field::subheading('section_subtitle')
        ->label('More details')
        ->tag('h5');

    $notice = Field::notice('notice_block')
        ->label('<strong>Attention</strong>')
        ->noticeType('warning');

    $content = Field::content('content_block')
        ->content('<p>Plain <strong>HTML</strong></p>');

    expect($group)->toBeInstanceOf(GroupField::class)
        ->and($group->render())->toContain('wp-field-group')
        ->and($group->render())->toContain('name="address[city]"')
        ->and($group->render())->toContain('name="address[street]"')
        ->and($group->toArray())->toMatchArray([
            'type' => 'group',
            'label' => 'Address',
        ])
        ->and($group->toArray()['fields'])->toHaveCount(2)
        ->and($heading)->toBeInstanceOf(HeadingField::class)
        ->and($heading->render())->toContain('<h2')
        ->and($heading->render())->toContain('Main Section')
        ->and($subheading)->toBeInstanceOf(SubheadingField::class)
        ->and($subheading->render())->toContain('<h5')
        ->and($subheading->render())->toContain('More details')
        ->and($notice)->toBeInstanceOf(NoticeField::class)
        ->and($notice->render())->toContain('wp-field-notice-warning')
        ->and($notice->render())->toContain('Attention')
        ->and($content)->toBeInstanceOf(ContentField::class)
        ->and($content->render())->toContain('<strong>HTML</strong>')
        ->and($content->render())->toContain('<p>Plain');
});

it('Field::make maps A2 simple interactive types to native classes', function (): void {
    $switcher = Field::make('switcher', 'enabled')
        ->textOn('Да')
        ->textOff('Нет')
        ->value('1');

    $spinner = Field::make('spinner', 'quantity')
        ->min(1)
        ->max(10)
        ->step(2)
        ->unit('шт')
        ->value(4);

    $buttonSet = Field::make('button_set', 'alignment')
        ->multiple()
        ->options(['left' => 'Left', 'right' => 'Right'])
        ->value(['left']);

    $slider = Field::make('slider', 'opacity')
        ->min(0)
        ->max(100)
        ->step(5)
        ->showValue()
        ->value(50);

    $imageSelect = Field::make('image_select', 'layout')
        ->options([
            'wide' => ['src' => 'https://example.com/wide.png', 'label' => 'Wide'],
            'boxed' => ['src' => 'https://example.com/boxed.png', 'label' => 'Boxed'],
        ])
        ->value('wide');

    expect($switcher)->toBeInstanceOf(SwitcherField::class)
        ->and($switcher->render())->toContain('wp-field-switcher')
        ->and($switcher->render())->toContain('checked')
        ->and($spinner)->toBeInstanceOf(SpinnerField::class)
        ->and($spinner->render())->toContain('wp-field-spinner')
        ->and($spinner->render())->toContain('min="1"')
        ->and($spinner->render())->toContain('max="10"')
        ->and($spinner->render())->toContain('step="2"')
        ->and($spinner->render())->toContain('wp-field-spinner-unit')
        ->and($buttonSet)->toBeInstanceOf(ButtonSetField::class)
        ->and($buttonSet->render())->toContain('wp-field-button-set')
        ->and($buttonSet->render())->toContain('type="checkbox"')
        ->and($buttonSet->render())->toContain('name="alignment[]"')
        ->and($slider)->toBeInstanceOf(SliderField::class)
        ->and($slider->render())->toContain('wp-field-slider-wrapper')
        ->and($slider->render())->toContain('wp-field-slider-value')
        ->and($imageSelect)->toBeInstanceOf(ImageSelectField::class)
        ->and($imageSelect->render())->toContain('wp-field-image-select')
        ->and($imageSelect->render())->toContain('selected')
        ->and($imageSelect->render())->toContain('wide.png');
});

it('RepeaterField рендерит вложенные имена без reflection-хаков', function (): void {
    $field = Field::repeater('items')
        ->addField(Field::text('title'))
        ->value([
            ['title' => 'Первая строка'],
        ]);

    $html = $field->render();

    expect($html)
        ->toContain('name="items[0][title]"')
        ->and($html)->toContain('value="Первая строка"');
});

it('FlexibleContentField рендерит вложенные имена layout-полей без reflection-хаков', function (): void {
    $field = Field::flexibleContent('content_blocks')
        ->addLayout('hero', 'Hero', [
            Field::text('headline'),
        ])
        ->value([
            [
                'acf_fc_layout' => 'hero',
                'headline' => 'Добро пожаловать',
            ],
        ]);

    $html = $field->render();

    expect($html)
        ->toContain('name="content_blocks[0][headline]"')
        ->and($html)->toContain('name="content_blocks[0][acf_fc_layout]"')
        ->and($html)->toContain('value="Добро пожаловать"');
});

it('Field::make maps layout container types accordion and tabbed to native classes', function (): void {
    $accordion = Field::make('accordion', 'faq')
        ->sections([
            ['title' => 'A', 'fields' => []],
        ]);

    $tabbed = Field::make('tabbed', 'settings_tabs')
        ->tabs([
            ['id' => 'general', 'title' => 'General', 'fields' => []],
        ]);

    expect($accordion)->toBeInstanceOf(AccordionField::class)
        ->and($accordion->getType())->toBe('accordion')
        ->and($tabbed)->toBeInstanceOf(TabbedField::class)
        ->and($tabbed->getType())->toBe('tabbed');
});

it('Field::make maps C1 settings object types to dedicated classes', function (): void {
    $typography = Field::make('typography', 'font')->attribute('options', ['font_size' => true]);
    $spacing = Field::make('spacing', 'padding')->attribute('units', ['px', 'em']);
    $dimensions = Field::make('dimensions', 'box')->attribute('units', ['px', '%']);
    $border = Field::make('border', 'frame')->attribute('styles', ['solid', 'dashed']);
    $background = Field::make('background', 'hero_bg')->attribute('background_fields', ['color' => true, 'image' => true]);
    $linkColor = Field::make('link_color', 'links')->attribute('states', ['regular', 'hover']);
    $colorGroup = Field::make('color_group', 'palette')->attribute('options', ['primary' => 'Primary']);

    expect($typography)->toBeInstanceOf(TypographyField::class)
        ->and($spacing)->toBeInstanceOf(SpacingField::class)
        ->and($dimensions)->toBeInstanceOf(DimensionsField::class)
        ->and($border)->toBeInstanceOf(BorderField::class)
        ->and($background)->toBeInstanceOf(BackgroundField::class)
        ->and($linkColor)->toBeInstanceOf(LinkColorField::class)
        ->and($colorGroup)->toBeInstanceOf(ColorGroupField::class)
        ->and($typography->getType())->toBe('typography')
        ->and($spacing->getType())->toBe('spacing')
        ->and($dimensions->getType())->toBe('dimensions')
        ->and($border->getType())->toBe('border')
        ->and($background->getType())->toBe('background')
        ->and($linkColor->getType())->toBe('link_color')
        ->and($colorGroup->getType())->toBe('color_group');
});

it('Field::make maps C2 advanced integration types to dedicated classes', function (): void {
    $codeEditor = Field::make('code_editor', 'custom_css')->attribute('mode', 'css');
    $icon = Field::make('icon', 'site_icon')->attribute('library', 'dashicons');
    $map = Field::make('map', 'store_map')->attribute('zoom', 12);
    $sortable = Field::make('sortable', 'enabled_blocks')->attribute('options', ['a' => 'A']);
    $sorter = Field::make('sorter', 'layout_sorter')->attribute('groups', ['enabled' => ['a' => 'A']]);
    $palette = Field::make('palette', 'theme_palette')->attribute('options', ['#000000', '#ffffff']);
    $link = Field::make('link', 'cta_link');
    $backup = Field::make('backup', 'options_backup');

    expect($codeEditor)->toBeInstanceOf(CodeEditorField::class)
        ->and($icon)->toBeInstanceOf(IconField::class)
        ->and($map)->toBeInstanceOf(MapField::class)
        ->and($sortable)->toBeInstanceOf(SortableField::class)
        ->and($sorter)->toBeInstanceOf(SorterField::class)
        ->and($palette)->toBeInstanceOf(PaletteField::class)
        ->and($link)->toBeInstanceOf(LinkField::class)
        ->and($backup)->toBeInstanceOf(BackupField::class)
        ->and($codeEditor->getType())->toBe('code_editor')
        ->and($icon->getType())->toBe('icon')
        ->and($map->getType())->toBe('map')
        ->and($sortable->getType())->toBe('sortable')
        ->and($sorter->getType())->toBe('sorter')
        ->and($palette->getType())->toBe('palette')
        ->and($link->getType())->toBe('link')
        ->and($backup->getType())->toBe('backup');
});

it('Field::make final alias map routes aliases to explicit classes', function (): void {
    $dateTime = Field::make('date_time', 'event_at');
    $datetimeAlias = Field::make('datetime', 'publish_at');
    $datetimeLocal = Field::make('datetime-local', 'start_at');
    $imagePicker = Field::make('image_picker', 'style');
    $imagePickerAlias = Field::make('imagepicker', 'style_alias');

    expect($dateTime->getType())->toBe('datetime-local')
        ->and($datetimeAlias->getType())->toBe('datetime-local')
        ->and($datetimeLocal->getType())->toBe('datetime-local')
        ->and($imagePicker)->toBeInstanceOf(ImagePickerField::class)
        ->and($imagePickerAlias)->toBeInstanceOf(ImagePickerField::class)
        ->and($imagePicker->getType())->toBe('image_picker')
        ->and($imagePickerAlias->getType())->toBe('image_picker');
});

it('Accordion and tabbed fields keep nested child config contract', function (): void {
    $accordion = Field::make('accordion', 'faq')->attribute('sections', [
        [
            'title' => 'Section A',
            'fields' => [
                ['id' => 'acc_title', 'type' => 'text', 'label' => 'Accordion Title'],
            ],
        ],
    ]);

    $tabbed = Field::make('tabbed', 'tabs')->attribute('tabs', [
        [
            'title' => 'General',
            'fields' => [
                ['id' => 'tab_title', 'type' => 'text', 'label' => 'Tab Title'],
            ],
        ],
    ]);

    $accordionArray = $accordion->toArray();
    $tabbedArray = $tabbed->toArray();

    expect($accordionArray['sections'][0]['fields'][0]['id'])->toBe('acc_title')
        ->and($tabbedArray['tabs'][0]['fields'][0]['id'])->toBe('tab_title');
});

it('layout container fields render nested content in native mode', function (): void {
    $accordion = Field::make('accordion', 'faq')
        ->label('FAQ')
        ->sections([
            [
                'title' => 'Section A',
                'open' => true,
                'fields' => [
                    ['id' => 'acc_title', 'type' => 'text', 'label' => 'Accordion Title'],
                ],
            ],
        ]);

    $tabbed = Field::make('tabbed', 'tabs')
        ->tabs([
            [
                'title' => 'General',
                'active' => true,
                'fields' => [
                    ['id' => 'tab_title', 'type' => 'text', 'label' => 'Tab Title'],
                ],
            ],
        ]);

    expect($accordion->render())
        ->toContain('wp-field-accordion')
        ->and($accordion->render())->toContain('aria-expanded="true"')
        ->and($accordion->render())->toContain('Accordion Title')
        ->and($tabbed->render())->toContain('wp-field-tabbed')
        ->and($tabbed->render())->toContain('role="tablist"')
        ->and($tabbed->render())->toContain('Tab Title');
});

it('sortable and sorter render ordered hidden inputs without legacy bridge', function (): void {
    $sortable = Field::make('sortable', 'blocks')
        ->attribute('options', ['hero' => 'Hero', 'gallery' => 'Gallery'])
        ->value(['gallery', 'hero']);

    $sorter = Field::make('sorter', 'columns')
        ->attribute('options', ['hero' => 'Hero', 'gallery' => 'Gallery', 'cta' => 'CTA'])
        ->attribute('groups', ['enabled' => 'Enabled', 'disabled' => 'Disabled'])
        ->value(['enabled' => ['hero'], 'disabled' => ['gallery']]);

    expect($sortable->render())
        ->toContain('wp-field-sortable')
        ->and($sortable->render())->toContain('name="blocks[]"')
        ->and($sortable->render())->toContain('value="gallery"')
        ->and($sorter->render())->toContain('wp-field-sorter')
        ->and($sorter->render())->toContain('data-type="enabled"')
        ->and($sorter->render())->toContain('name="columns[enabled][]"')
        ->and($sorter->render())->toContain('name="columns[disabled][]"');
});

it('color_group keeps grouped values in render and sanitize contract', function (): void {
    $field = Field::make('color_group', 'palette')
        ->attribute('options', ['primary' => 'Primary', 'accent' => 'Accent'])
        ->value(['primary' => '#111111', 'accent' => '#ff0000']);

    $html = $field->render();

    expect($html)
        ->toContain('wp-field-color-group')
        ->and($html)->toContain('name="palette[primary]"')
        ->and($html)->toContain('value="#111111"')
        ->and($field->sanitize(['primary' => ' <b>#fff</b> ']))->toBe(['primary' => '#fff']);
});

it('Field preserves conditional logic structure', function (): void {
    $field = Field::make('color', 'accent')
        ->when('layout', '==', 'modern')
        ->orWhen('layout', '==', 'compact');

    expect($field->getConditions())
        ->toHaveCount(2)
        ->and($field->getConditions()[0]['field'])->toBe('layout')
        ->and($field->getConditions()[1]['logic'])->toBe('OR');
});
