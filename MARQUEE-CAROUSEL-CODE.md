# Marquee Scrolling Carousel for ACF Repeater Images

## Setup Instructions

1. **Update the ACF JSON location**: Replace `YOUR_HOME_PAGE_ID` in `group_home_page.json` with your actual home page ID
   - Go to Pages → Home Page
   - Look at the URL: `post.php?post=123&action=edit` (123 is the page ID)
   - Replace `YOUR_HOME_PAGE_ID` with that number

2. **Sync ACF Field Group**: 
   - Go to ACF → Field Groups
   - Find "Home Page" and click "Sync" if needed

## Divi Code Module Code

Paste this code into a Divi Code Module on your home page:

```html
<style>
.marquee-container {
    width: 100%;
    overflow: hidden;
    position: relative;
    padding: 40px 0;
}

.marquee-track {
    display: flex;
    gap: 30px;
    animation: marquee-scroll 30s linear infinite;
    width: fit-content;
}

.marquee-track:hover {
    animation-play-state: paused;
}

.marquee-item {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.marquee-item img {
    max-width: 200px;
    height: auto;
    object-fit: contain;
    filter: grayscale(100%);
    opacity: 0.7;
    transition: all 0.3s ease;
}

.marquee-item img:hover {
    filter: grayscale(0%);
    opacity: 1;
    transform: scale(1.05);
}

/* Duplicate content for seamless loop */
.marquee-track-duplicate {
    display: flex;
    gap: 30px;
    animation: marquee-scroll 30s linear infinite;
    width: fit-content;
    animation-delay: 15s;
}

@keyframes marquee-scroll {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .marquee-item img {
        max-width: 150px;
    }
    
    .marquee-track {
        gap: 20px;
    }
}
</style>

<div class="marquee-container">
    <div class="marquee-wrapper">
        <div class="marquee-track">
            <!-- Repeater images will be inserted here -->
        </div>
        <div class="marquee-track-duplicate">
            <!-- Duplicate for seamless loop -->
        </div>
    </div>
</div>

<script>
(function() {
    // Get repeater field data
    const repeaterField = 'test_repeater'; // Change this to your repeater field name
    const imageField = 'image'; // Change this to your image sub-field name
    
    // This will be replaced by the merge tag system
    // For now, we'll use PHP to generate the HTML
    
    // The merge tags will process this, so we need to use PHP in the code module
})();
</script>
```

## PHP Version (Recommended)

Since Divi Code Modules support PHP, use this version instead:

```php
<?php
// Get the repeater field
$repeater = get_field('test_repeater'); // Change 'test_repeater' to your actual repeater field name
$image_field = 'image'; // Change 'image' to your actual image sub-field name

if ($repeater && is_array($repeater)) {
    ?>
    <style>
    .marquee-container {
        width: 100%;
        overflow: hidden;
        position: relative;
        padding: 40px 0;
        background: #f5f5f5; /* Optional background */
    }

    .marquee-wrapper {
        display: flex;
        width: 200%; /* Double width for seamless loop */
    }

    .marquee-track {
        display: flex;
        gap: 30px;
        animation: marquee-scroll 30s linear infinite;
        width: 50%; /* Half width since wrapper is 200% */
    }

    .marquee-track-duplicate {
        display: flex;
        gap: 30px;
        width: 50%;
    }

    .marquee-track:hover,
    .marquee-track-duplicate:hover {
        animation-play-state: paused;
    }

    .marquee-item {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .marquee-item img {
        max-width: 200px;
        height: auto;
        object-fit: contain;
        filter: grayscale(100%);
        opacity: 0.7;
        transition: all 0.3s ease;
    }

    .marquee-item img:hover {
        filter: grayscale(0%);
        opacity: 1;
        transform: scale(1.05);
    }

    @keyframes marquee-scroll {
        0% {
            transform: translateX(0);
        }
        100% {
            transform: translateX(-100%);
        }
    }

    @media (max-width: 768px) {
        .marquee-item img {
            max-width: 150px;
        }
        
        .marquee-track {
            gap: 20px;
        }
    }
    </style>

    <div class="marquee-container">
        <div class="marquee-wrapper">
            <div class="marquee-track">
                <?php 
                // First set of images
                foreach ($repeater as $row) {
                    if (isset($row[$image_field]) && is_array($row[$image_field])) {
                        $image = $row[$image_field];
                        ?>
                        <div class="marquee-item">
                            <img src="<?php echo esc_url($image['url']); ?>" 
                                 alt="<?php echo esc_attr($image['alt'] ?: ''); ?>"
                                 title="<?php echo esc_attr($image['title'] ?: ''); ?>">
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <div class="marquee-track-duplicate">
                <?php 
                // Duplicate set for seamless loop
                foreach ($repeater as $row) {
                    if (isset($row[$image_field]) && is_array($row[$image_field])) {
                        $image = $row[$image_field];
                        ?>
                        <div class="marquee-item">
                            <img src="<?php echo esc_url($image['url']); ?>" 
                                 alt="<?php echo esc_attr($image['alt'] ?: ''); ?>"
                                 title="<?php echo esc_attr($image['title'] ?: ''); ?>">
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>
    <?php
}
?>
```

## Using Merge Tags (Alternative)

If you prefer to use the merge tag system, you can use this HTML version:

```html
<style>
.marquee-container {
    width: 100%;
    overflow: hidden;
    position: relative;
    padding: 40px 0;
}

.marquee-wrapper {
    display: flex;
    width: 200%;
}

.marquee-track {
    display: flex;
    gap: 30px;
    animation: marquee-scroll 30s linear infinite;
    width: 50%;
}

.marquee-track-duplicate {
    display: flex;
    gap: 30px;
    width: 50%;
}

.marquee-item {
    flex-shrink: 0;
}

.marquee-item img {
    max-width: 200px;
    height: auto;
    object-fit: contain;
}

@keyframes marquee-scroll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-100%); }
}
</style>

<div class="marquee-container">
    <div class="marquee-wrapper">
        <div class="marquee-track">
            <!-- You'll need to manually add merge tags for each row -->
            <div class="marquee-item">
                <img src="{acf:test_repeater:0:image}" alt="{acf:test_repeater:0:image:alt}">
            </div>
            <div class="marquee-item">
                <img src="{acf:test_repeater:1:image}" alt="{acf:test_repeater:1:image:alt}">
            </div>
            <!-- Add more rows as needed -->
        </div>
        <div class="marquee-track-duplicate">
            <!-- Duplicate the same images for seamless loop -->
            <div class="marquee-item">
                <img src="{acf:test_repeater:0:image}" alt="{acf:test_repeater:0:image:alt}">
            </div>
            <div class="marquee-item">
                <img src="{acf:test_repeater:1:image}" alt="{acf:test_repeater:1:image:alt}">
            </div>
        </div>
    </div>
</div>
```

**Note**: The merge tag version requires manually adding each row. The PHP version automatically loops through all repeater rows.

## Customization

- **Speed**: Change `30s` in `animation: marquee-scroll 30s` (lower = faster)
- **Image size**: Change `max-width: 200px` in `.marquee-item img`
- **Gap**: Change `gap: 30px` in `.marquee-track`
- **Grayscale effect**: Remove `filter: grayscale(100%)` if you don't want it
- **Background**: Change `background: #f5f5f5` in `.marquee-container`
