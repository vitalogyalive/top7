<?php
namespace Top7\Display;

use Top7\Security\CsrfToken;

/**
 * Modern form components using Tailwind CSS
 */
class Form {

    /**
     * Start a form with CSRF protection
     */
    public static function start(string $action, string $method = 'post', array $options = []): void {
        $id = $options['id'] ?? '';
        $class = $options['class'] ?? '';
        $enctype = isset($options['enctype']) ? "enctype=\"{$options['enctype']}\"" : '';
        ?>
        <form
            action="<?= htmlspecialchars($action) ?>"
            method="<?= htmlspecialchars($method) ?>"
            <?= $id ? "id=\"$id\"" : '' ?>
            <?= $class ? "class=\"$class\"" : '' ?>
            <?= $enctype ?>
        >
            <?php if (class_exists('\Top7\Security\CsrfToken')): ?>
                <?= CsrfToken::field() ?>
            <?php endif; ?>
        <?php
    }

    /**
     * End form
     */
    public static function end(): void {
        ?>
        </form>
        <?php
    }

    /**
     * Render a text input
     */
    public static function input(string $name, array $options = []): void {
        $type = $options['type'] ?? 'text';
        $label = $options['label'] ?? '';
        $value = $options['value'] ?? '';
        $placeholder = $options['placeholder'] ?? '';
        $required = isset($options['required']) && $options['required'];
        $disabled = isset($options['disabled']) && $options['disabled'];
        $readonly = isset($options['readonly']) && $options['readonly'];
        $error = $options['error'] ?? '';
        $hint = $options['hint'] ?? '';
        $id = $options['id'] ?? $name;
        ?>
        <div class="mb-4">
            <?php if ($label): ?>
            <label for="<?= htmlspecialchars($id) ?>" class="block text-sm font-medium text-gray-700 mb-1">
                <?= htmlspecialchars($label) ?>
                <?php if ($required): ?><span class="text-red-500">*</span><?php endif; ?>
            </label>
            <?php endif; ?>
            <input
                type="<?= htmlspecialchars($type) ?>"
                id="<?= htmlspecialchars($id) ?>"
                name="<?= htmlspecialchars($name) ?>"
                value="<?= htmlspecialchars($value) ?>"
                placeholder="<?= htmlspecialchars($placeholder) ?>"
                <?= $required ? 'required' : '' ?>
                <?= $disabled ? 'disabled' : '' ?>
                <?= $readonly ? 'readonly' : '' ?>
                class="input-field <?= $error ? 'border-red-500 focus:ring-red-500' : '' ?> <?= $disabled ? 'bg-gray-100' : '' ?>"
            >
            <?php if ($error): ?>
            <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars($error) ?></p>
            <?php elseif ($hint): ?>
            <p class="mt-1 text-sm text-gray-500"><?= htmlspecialchars($hint) ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render a select dropdown
     */
    public static function select(string $name, array $options = [], array $config = []): void {
        $label = $config['label'] ?? '';
        $selected = $config['selected'] ?? '';
        $required = isset($config['required']) && $config['required'];
        $disabled = isset($config['disabled']) && $config['disabled'];
        $placeholder = $config['placeholder'] ?? 'Sélectionner...';
        $id = $config['id'] ?? $name;
        $onChange = $config['onChange'] ?? '';
        ?>
        <div class="mb-4">
            <?php if ($label): ?>
            <label for="<?= htmlspecialchars($id) ?>" class="block text-sm font-medium text-gray-700 mb-1">
                <?= htmlspecialchars($label) ?>
                <?php if ($required): ?><span class="text-red-500">*</span><?php endif; ?>
            </label>
            <?php endif; ?>
            <select
                id="<?= htmlspecialchars($id) ?>"
                name="<?= htmlspecialchars($name) ?>"
                <?= $required ? 'required' : '' ?>
                <?= $disabled ? 'disabled' : '' ?>
                <?= $onChange ? "onchange=\"$onChange\"" : '' ?>
                class="input-field <?= $disabled ? 'bg-gray-100' : '' ?>"
            >
                <?php if ($placeholder): ?>
                <option value=""><?= htmlspecialchars($placeholder) ?></option>
                <?php endif; ?>
                <?php foreach ($options as $value => $text): ?>
                <option value="<?= htmlspecialchars($value) ?>" <?= ($value == $selected) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($text) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    /**
     * Render a textarea
     */
    public static function textarea(string $name, array $options = []): void {
        $label = $options['label'] ?? '';
        $value = $options['value'] ?? '';
        $placeholder = $options['placeholder'] ?? '';
        $required = isset($options['required']) && $options['required'];
        $rows = $options['rows'] ?? 4;
        $id = $options['id'] ?? $name;
        ?>
        <div class="mb-4">
            <?php if ($label): ?>
            <label for="<?= htmlspecialchars($id) ?>" class="block text-sm font-medium text-gray-700 mb-1">
                <?= htmlspecialchars($label) ?>
                <?php if ($required): ?><span class="text-red-500">*</span><?php endif; ?>
            </label>
            <?php endif; ?>
            <textarea
                id="<?= htmlspecialchars($id) ?>"
                name="<?= htmlspecialchars($name) ?>"
                placeholder="<?= htmlspecialchars($placeholder) ?>"
                rows="<?= (int)$rows ?>"
                <?= $required ? 'required' : '' ?>
                class="input-field"
            ><?= htmlspecialchars($value) ?></textarea>
        </div>
        <?php
    }

    /**
     * Render a checkbox
     */
    public static function checkbox(string $name, array $options = []): void {
        $label = $options['label'] ?? '';
        $checked = isset($options['checked']) && $options['checked'];
        $value = $options['value'] ?? '1';
        $id = $options['id'] ?? $name;
        ?>
        <div class="mb-4 flex items-center">
            <input
                type="checkbox"
                id="<?= htmlspecialchars($id) ?>"
                name="<?= htmlspecialchars($name) ?>"
                value="<?= htmlspecialchars($value) ?>"
                <?= $checked ? 'checked' : '' ?>
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
            >
            <?php if ($label): ?>
            <label for="<?= htmlspecialchars($id) ?>" class="ml-2 block text-sm text-gray-900">
                <?= htmlspecialchars($label) ?>
            </label>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render hidden input
     */
    public static function hidden(string $name, string $value): void {
        ?>
        <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($value) ?>">
        <?php
    }

    /**
     * Render a submit button
     */
    public static function submit(string $text = 'Envoyer', array $options = []): void {
        $type = $options['type'] ?? 'primary';
        $name = $options['name'] ?? '';
        $class = $options['class'] ?? '';
        $disabled = isset($options['disabled']) && $options['disabled'];
        $icon = $options['icon'] ?? '';

        $btnClass = match($type) {
            'secondary' => 'btn btn-secondary',
            'danger' => 'btn btn-danger',
            'success' => 'btn btn-success',
            default => 'btn btn-primary'
        };
        ?>
        <button
            type="submit"
            <?= $name ? "name=\"$name\"" : '' ?>
            <?= $disabled ? 'disabled' : '' ?>
            class="<?= $btnClass ?> <?= htmlspecialchars($class) ?>"
        >
            <?php if ($icon): ?>
            <span class="mr-2"><?= $icon ?></span>
            <?php endif; ?>
            <?= htmlspecialchars($text) ?>
        </button>
        <?php
    }

    /**
     * Render form actions (button group)
     */
    public static function actions(): void {
        ?>
        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200 mt-6">
        <?php
    }

    /**
     * End form actions
     */
    public static function endActions(): void {
        ?>
        </div>
        <?php
    }

    /**
     * Render a button link
     */
    public static function link(string $text, string $href, array $options = []): void {
        $type = $options['type'] ?? 'secondary';
        $class = $options['class'] ?? '';

        $btnClass = match($type) {
            'primary' => 'btn btn-primary',
            'danger' => 'btn btn-danger',
            default => 'btn btn-secondary'
        };
        ?>
        <a href="<?= htmlspecialchars($href) ?>" class="<?= $btnClass ?> <?= htmlspecialchars($class) ?>">
            <?= htmlspecialchars($text) ?>
        </a>
        <?php
    }
}
