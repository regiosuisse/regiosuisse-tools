# %INSTANCE_ID%Feedback

## Setup

### Import CSS (optional)

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/feedback-latest/embed-feedback-latest.css">
```

### Import JavaScript

```html
<script src="%HOST%/embed/feedback-latest/embed-feedback-latest.js"></script>
```

## Basic Usage

```html
<div id="%INSTANCE_ID%-feedback"></div>
```

```javascript
%INSTANCE_ID%Feedback('#%INSTANCE_ID%-feedback', {
    // options
});
```

## Customization

### Options

| Option              | Type      | Description                                                                              | Example                                                    |
|---------------------|-----------|------------------------------------------------------------------------------------------|------------------------------------------------------------|
| `locale`            | `String`  | Set the locale.                                                                          | `"de"`                                                     |
| `responsive`        | `Boolean` | Whether responsive media queries should be enabled.                                      | `true`                                                     |

### Styling

Basic styles can be overwritten using CSS variables:

```css
.embed-feedback {
    --%INSTANCE_ID%-max-width: none;
    --%INSTANCE_ID%-gutter-width: 1em;
    --%INSTANCE_ID%-primary-color: #0059be;
    --%INSTANCE_ID%-secondary-color: #92bae2;
    --%INSTANCE_ID%-border-radius-1: 0;
    --%INSTANCE_ID%-border-radius-2: 0;
}
```

> For more available variables have a look at %HOST%/embed/feedback-latest/embed-feedback-latest.css

## Full Example

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/feedback-latest/embed-feedback-latest.css">

<script src="%HOST%/embed/feedback-latest/embed-feedback-latest.js"></script>

<div id="%INSTANCE_ID%-feedback"></div>

<style>
    .embed-feedback {
        --%INSTANCE_ID%-primary-color: #0059be;
        --%INSTANCE_ID%-secondary-color: #92bae2;
    }
</style>

<script>
    %INSTANCE_ID%Feedback('#%INSTANCE_ID%-feedback', {
        locale: 'fr',
        responsive: true,
        middleware: {},
    });
</script>
```