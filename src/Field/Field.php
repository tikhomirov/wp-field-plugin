<?php

declare(strict_types=1);

namespace WpField\Field;

use WpField\Field\Types\AccordionField;
use WpField\Field\Types\BackgroundField;
use WpField\Field\Types\BackupField;
use WpField\Field\Types\BorderField;
use WpField\Field\Types\ButtonSetField;
use WpField\Field\Types\CheckboxField;
use WpField\Field\Types\CheckboxGroupField;
use WpField\Field\Types\CodeEditorField;
use WpField\Field\Types\ColorField;
use WpField\Field\Types\ColorGroupField;
use WpField\Field\Types\ContentField;
use WpField\Field\Types\DimensionsField;
use WpField\Field\Types\EditorField;
use WpField\Field\Types\FieldsetField;
use WpField\Field\Types\FileField;
use WpField\Field\Types\FlexibleContentField;
use WpField\Field\Types\GalleryField;
use WpField\Field\Types\GroupField;
use WpField\Field\Types\HeadingField;
use WpField\Field\Types\IconField;
use WpField\Field\Types\ImageField;
use WpField\Field\Types\ImagePickerField;
use WpField\Field\Types\ImageSelectField;
use WpField\Field\Types\InputField;
use WpField\Field\Types\LegacyWrapperField;
use WpField\Field\Types\LinkColorField;
use WpField\Field\Types\LinkField;
use WpField\Field\Types\MapField;
use WpField\Field\Types\MediaField;
use WpField\Field\Types\NoticeField;
use WpField\Field\Types\PaletteField;
use WpField\Field\Types\RadioField;
use WpField\Field\Types\RepeaterField;
use WpField\Field\Types\SelectField;
use WpField\Field\Types\SliderField;
use WpField\Field\Types\SortableField;
use WpField\Field\Types\SorterField;
use WpField\Field\Types\SpacingField;
use WpField\Field\Types\SpinnerField;
use WpField\Field\Types\SubheadingField;
use WpField\Field\Types\SwitcherField;
use WpField\Field\Types\TabbedField;
use WpField\Field\Types\TextareaField;
use WpField\Field\Types\TextField;
use WpField\Field\Types\TypographyField;

class Field
{
    public static function text(string $name): TextField
    {
        return new TextField($name);
    }

    public static function group(string $name): GroupField
    {
        return new GroupField($name);
    }

    public static function repeater(string $name): RepeaterField
    {
        return new RepeaterField($name);
    }

    public static function flexibleContent(string $name): FlexibleContentField
    {
        return new FlexibleContentField($name);
    }

    public static function heading(string $name): HeadingField
    {
        return new HeadingField($name);
    }

    public static function subheading(string $name): SubheadingField
    {
        return new SubheadingField($name);
    }

    public static function notice(string $name): NoticeField
    {
        return new NoticeField($name);
    }

    public static function content(string $name): ContentField
    {
        return new ContentField($name);
    }

    public static function radio(string $name): RadioField
    {
        return new RadioField($name);
    }

    public static function media(string $name): MediaField
    {
        return new MediaField($name);
    }

    public static function fieldset(string $name): FieldsetField
    {
        return new FieldsetField($name);
    }

    /**
     * Create a legacy field wrapper for types not yet supported by native OOP API
     * (e.g. select, checkbox)
     */
    public static function legacy(string $type, string $name): LegacyWrapperField
    {
        return new LegacyWrapperField($name, $type);
    }

    public static function make(string $type, string $name): FieldInterface
    {
        $normalizedType = match ($type) {
            'date_time', 'datetime' => 'datetime-local',
            'imagepicker' => 'image_picker',
            default => $type,
        };

        return match ($normalizedType) {
            'text' => self::text($name),
            'password', 'email', 'url', 'tel', 'number', 'range', 'hidden', 'date', 'time', 'datetime-local' => new InputField($name, $normalizedType),
            'textarea' => new TextareaField($name),
            'select' => new SelectField($name),
            'multiselect' => (new SelectField($name))->multiple(),
            'radio' => self::radio($name),
            'checkbox' => new CheckboxField($name),
            'checkbox_group' => new CheckboxGroupField($name),
            'group' => self::group($name),
            'switcher' => new SwitcherField($name),
            'spinner' => new SpinnerField($name),
            'button_set' => new ButtonSetField($name),
            'slider' => new SliderField($name),
            'image_select' => new ImageSelectField($name),
            'image_picker' => new ImagePickerField($name),
            'heading' => self::heading($name),
            'subheading' => self::subheading($name),
            'notice' => self::notice($name),
            'content' => self::content($name),
            'color' => new ColorField($name),
            'editor' => new EditorField($name),
            'image' => new ImageField($name),
            'file' => new FileField($name),
            'gallery' => new GalleryField($name),
            'repeater' => self::repeater($name),
            'flexible_content' => self::flexibleContent($name),
            'media' => self::media($name),
            'fieldset' => self::fieldset($name),
            'accordion' => new AccordionField($name),
            'tabbed' => new TabbedField($name),
            'typography' => new TypographyField($name),
            'spacing' => new SpacingField($name),
            'dimensions' => new DimensionsField($name),
            'border' => new BorderField($name),
            'background' => new BackgroundField($name),
            'link_color' => new LinkColorField($name),
            'color_group' => new ColorGroupField($name),
            'code_editor' => new CodeEditorField($name),
            'icon' => new IconField($name),
            'map' => new MapField($name),
            'sortable' => new SortableField($name),
            'sorter' => new SorterField($name),
            'palette' => new PaletteField($name),
            'link' => new LinkField($name),
            'backup' => new BackupField($name),
            default => self::legacy($type, $name),
        };
    }
}
