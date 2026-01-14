# Marquee Carousel Shortcode for Divi Code Modules

Since PHP doesn't work in Divi Code Modules, I've created a shortcode that you can use instead.

## Usage in Divi Code Module

Simply paste this shortcode into your Divi Code Module:

```
[marquee_carousel repeater="test_repeater" image="image"]
```

## Shortcode Parameters

- `repeater` - Your ACF repeater field name (default: `test_repeater`)
- `image` - Your image sub-field name within the repeater (default: `image`)
- `speed` - Animation speed in seconds (default: `30`)
- `max_width` - Maximum image width in pixels (default: `200`)

## Examples

**Basic usage:**

```
[marquee_carousel]
```

**Custom repeater and image field:**

```
[marquee_carousel repeater="client_logos" image="logo"]
```

**Custom speed and size:**

```
[marquee_carousel repeater="test_repeater" image="image" speed="20" max_width="250"]
```

**Full example:**

```
[marquee_carousel repeater="test_repeater" image="image" speed="25" max_width="180"]
```

## How It Works

The shortcode:

1. Fetches your ACF repeater field data
2. Loops through all rows
3. Displays images in a seamless scrolling marquee
4. Duplicates the images for infinite loop effect
5. Includes hover effects (grayscale to color, scale on hover)

## Features

- ✅ Seamless infinite scroll
- ✅ Hover to pause animation
- ✅ Grayscale effect (removes on hover)
- ✅ Responsive design
- ✅ Customizable speed and size
- ✅ Works in Divi Code Modules (no PHP needed)

## Customization

The shortcode generates unique CSS classes based on your repeater field name, so you can have multiple carousels on the same page without conflicts.

If you need to customize the styling further, you can add custom CSS targeting the generated classes:

- `.marquee-container-{repeater_name}`
- `.marquee-track-{repeater_name}`
- `.marquee-item-{repeater_name}`
