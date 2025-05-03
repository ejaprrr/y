<?php
/**
 * Reusable form input component
 * @param string $type Input type (text, password, etc)
 * @param string $name Input name
 * @param string $id Input ID
 * @param string $label Input label
 * @param string $value Current value
 * @param string $placeholder Input placeholder
 * @param string $icon Bootstrap icon class without the 'bi-' prefix
 * @param bool $required Whether input is required
 */

// Initialize defaults
$type = $type ?? 'text';
$required = $required ?? false;
$icon = $icon ?? null;
$value = $value ?? '';
$placeholder = $placeholder ?? '';
?>

<div class="mb-4">
    <?php if (isset($label)): ?>
        <label for="<?php echo $id; ?>" class="form-label fw-semibold"><?php echo htmlspecialchars($label); ?></label>
    <?php endif; ?>
    
    <?php if ($icon): ?>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0">
                <i class="bi bi-<?php echo $icon; ?>"></i>
            </span>
            <input 
                type="<?php echo $type; ?>" 
                class="form-control border-start-0 ps-0 <?php echo isset($size) ? "form-control-$size" : ''; ?>" 
                id="<?php echo $id; ?>" 
                name="<?php echo $name; ?>" 
                value="<?php echo htmlspecialchars($value); ?>"
                placeholder="<?php echo htmlspecialchars($placeholder); ?>"
                <?php echo $required ? 'required' : ''; ?>
                <?php echo isset($maxlength) ? "maxlength=\"$maxlength\"" : ''; ?>
                <?php echo isset($autocomplete) ? "autocomplete=\"$autocomplete\"" : ''; ?>
            >
        </div>
    <?php else: ?>
        <input 
            type="<?php echo $type; ?>" 
            class="form-control <?php echo isset($size) ? "form-control-$size" : ''; ?>" 
            id="<?php echo $id; ?>" 
            name="<?php echo $name; ?>" 
            value="<?php echo htmlspecialchars($value); ?>"
            placeholder="<?php echo htmlspecialchars($placeholder); ?>"
            <?php echo $required ? 'required' : ''; ?>
            <?php echo isset($maxlength) ? "maxlength=\"$maxlength\"" : ''; ?>
            <?php echo isset($autocomplete) ? "autocomplete=\"$autocomplete\"" : ''; ?>
        >
    <?php endif; ?>
    
    <?php if (isset($help_text)): ?>
        <div class="form-text"><?php echo $help_text; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_id)): ?>
        <div id="<?php echo $error_id; ?>" class="form-text text-danger"></div>
    <?php endif; ?>
</div>