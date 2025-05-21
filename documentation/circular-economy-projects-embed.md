# %INSTANCE_ID%Projects

## Setup

### Import CSS (optional)

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/circular-economy-projects-latest/embed-circular-economy-projects-latest.css">
```

### Import JavaScript

```html
<script src="%HOST%/embed/circular-economy-projects-latest/embed-circular-economy-projects-latest.js"></script>
```

## Basic Usage

```html
<div id="%INSTANCE_ID%-circular-economy-projects"></div>
```

```javascript
%INSTANCE_ID%Projects('#%INSTANCE_ID%-circular-economy-projects', {
    // options
});
```

## Customization

### Options

| Option             | Type      | Description                                                                              | Example                                                                 |
|--------------------|-----------|------------------------------------------------------------------------------------------|-------------------------------------------------------------------------|
| `locale`           | `String`  | Set the locale.                                                                          | `"de"`                                                                  |
| `limit`            | `Number`  | Set the pagination limit.                                                                | `30`                                                                    |
| `fixedFilters`     | `Array`   | Set fixed filter options. These aren't visible to the user.                              | `[{ type: "topic", entity: { id: 2 } }]`                                |
| `defaultFilters`   | `Array`   | Preselect specific filter options.                                                       | `[{ type: "program", entity: { id: 1 } }]`                              |
| `responsive`       | `Boolean` | Whether responsive media queries should be enabled.                                      | `true`                                                                  |
| `middleware`       | `Object`  | (Experimental) Middleware options allow you to define custom methods to manipulate data. | `{ filterTopics: topic => topic.name !== "Tourism" }`                   |
| `disableTelemetry` | `Boolean` | Disable collection of telemetry data.                                                    | `true`                                                                  |
| `history`          | `Object`  | Enable browser history support.                                                          | `{ mode: 'hash', base: 'http://localhost/projects' }`                   |
| `templateHooks`    | `Object`  | Insert html into template hooks.                                                         | `{ projectContentAfter: (instance, project) => '<p>Hello World!</p>' }` |

### Styling

Basic styles can be overwritten using CSS variables:

```css
.embed-projects {
    --%INSTANCE_ID%-max-width: none;
    --%INSTANCE_ID%-gutter-width: 1em;
    --%INSTANCE_ID%-primary-color: #0059be;
    --%INSTANCE_ID%-secondary-color: #92bae2;
    --%INSTANCE_ID%-border-radius-1: 0;
    --%INSTANCE_ID%-border-radius-2: 0;
}
```

> For more available variables have a look at %HOST%/embed/circular-economy-projects-latest/embed-circular-economy-projects-latest.css

## Full Example

```html
<link rel="stylesheet" type="text/css" href="%HOST%/embed/projects-latest/embed-circular-economy-projects-latest.css">

<script src="%HOST%/embed/circular-economy-projects-latest/embed-circular-economy-projects-latest.js"></script>

<div id="%INSTANCE_ID%-circular-economy-projects"></div>

<style>
    .embed-circular-economy-projects {
        --%INSTANCE_ID%-primary-color: #0059be;
        --%INSTANCE_ID%-secondary-color: #92bae2;
    }
</style>

<script>
    %INSTANCE_ID%CircularEconomyProjects('#%INSTANCE_ID%-circular-economy-projects', {
        locale: 'fr',
        limit: 6,
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
                type: 'instrument', 
                entity: { 
                    id: 1,
                } 
            }
        ],
        middleware: {
            modifyQueryParams: params => ({ ...params, randomize: 0 })
            mapTopics: topic => ({ ...topic, name: topic.name === 'Foo' ? 'Bar' : topic.name }),
            filterTopics: topic => topic.id !== 1,
            sortTopics: (a, b) => a.name.localeCompare(b.name),
            // mapBusinessSectors
            // filterBusinessSectors
            // sortBusinessSectors
            // mapInstruments
            // filterInstruments
            // sortInstruments
            // mapInstruments
            // filterInstruments
            // sortInstruments
        },
        templateHooks: {
            projectContentAfter: (instance, project) => '<p>The project ID is: '+project.id+'</p>',
            // projectsBefore: function (instance, locale) {}
            // projectsAfter: function (instance, locale) {}
            // projectContentBefore: function (instance, project) {}
        },
    });
</script>
```