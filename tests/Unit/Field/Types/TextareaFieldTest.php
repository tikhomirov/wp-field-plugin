<?php

declare(strict_types=1);

use WpField\Field\Types\TextareaField;

beforeEach(function (): void {
    $this->field = new TextareaField('test_textarea');
});

it('renders with description', function (): void {
    $this->field->description('Enter your text here');
    $html = $this->field->render();

    expect($html)->toContain('Enter your text here')
        ->and($html)->toContain('description');
});
