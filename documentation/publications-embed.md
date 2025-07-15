# %INSTANCE_ID%Publications

## Setup

### Import CSS (optional)

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/publications-latest/embed-publications-latest.css">
```

### Import JavaScript

```html
<script src="%HOST%/embed/publications-latest/embed-publications-latest.js"></script>
```

## Basic Usage

```html
<div id="%INSTANCE_ID%-publications"></div>
```

```javascript
%INSTANCE_ID%Publications('#%INSTANCE_ID%-publications', {
    // options
});
```

## Customization

### Options

| Option              | Type      | Description                                                                              | Example                                                    |
|---------------------|-----------|------------------------------------------------------------------------------------------|------------------------------------------------------------|
| `locale`            | `String`  | Set the locale.                                                                          | `"de"`                                                     |
| `fixedFilters`      | `Array`   | Set fixed filter options. These aren't visible to the user.                              | `[{ type: "topic", entity: { id: 2 } }]`                   |
| `defaultFilters`    | `Array`   | Preselect specific filter options.                                                       | `[{ type: "geographicRegion", entity: { id: 1 } }]`                |
| `responsive`        | `Boolean` | Whether responsive media queries should be enabled.                                      | `true`                                                     |
| `middleware`        | `Object`  | (Experimental) Middleware options allow you to define custom methods to manipulate data. | `{ filterGeographicRegions: geographicRegion => geographicRegion.name !== "Foo" }` |
| `disableTelemetry`  | `Boolean` | Disable collection of telemetry data.                                                    | `true`                                                     |
| `history`           | `Object`  | Enable browser history support.                                                          | `{ mode: 'hash', base: 'http://localhost/publications' }`          |

### Styling

Basic styles can be overwritten using CSS variables:

```css
.embed-publications {
    --%INSTANCE_ID%-max-width: none;
    --%INSTANCE_ID%-gutter-width: 1em;
    --%INSTANCE_ID%-primary-color: #0059be;
    --%INSTANCE_ID%-secondary-color: #92bae2;
    --%INSTANCE_ID%-border-radius-1: 0;
    --%INSTANCE_ID%-border-radius-2: 0;
}
```

> For more available variables have a look at %HOST%/embed/publications-latest/embed-publications-latest.css

## Full Example

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/publications-latest/embed-publications-latest.css">

<script src="%HOST%/embed/publications-latest/embed-publications-latest.js"></script>

<div id="%INSTANCE_ID%-publications"></div>

<style>
    .embed-publications {
        --%INSTANCE_ID%-primary-color: #0059be;
        --%INSTANCE_ID%-secondary-color: #92bae2;
    }
</style>

<script>
    %INSTANCE_ID%Publications('#%INSTANCE_ID%-publications', {
        locale: 'fr',
        responsive: true,
        fixedFilters: [
            { 
                type: 'topic', 
                entity: { 
                    id: 1,
                } 
            }
        ],
        defaultFilters: [
            { 
                type: 'geographicRegion', 
                entity: { 
                    id: 1,
                } 
            }
        ],
        middleware: {
            mapGeographicRegions: geographicRegion => ({ ...geographicRegion, name: geographicRegion.name === 'Foo' ? 'Bar' : geographicRegion.name }),
            filterGeographicRegions: geographicRegion => geographicRegion.id !== 1,
            sortGeographicRegions: (a, b) => a.name.localeCompare(b.name),
            // mapTopics
            // filterTopics
            // sortTopics
            // mapLanguages
            // filterLanguages
            // sortLanguages
        },
    });
</script>
```