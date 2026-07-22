# Rich text ↔ Media Library bridge

Do not add a second image picker for editors. Always use `window.DashboardMedia`.

## JS API

```js
window.DashboardMedia.open({
  multiple: false,
  type: 'image', // reserved for future video/document filters
  onSelect: (item) => {
    // item.id, item.url, item.thumbnail_url, item.alt
    editor.insertContent(
      `<img src="${item.url}" alt="${item.alt || ''}" data-media-id="${item.id}" />`
    );
  },
});
```

Prefer storing **`data-media-id`** (or a shortcode) in HTML so CDN URL changes do not orphan content. Resolve display URLs server-side from `MediaAsset` when rendering.

## TinyMCE

```js
tinymce.init({
  file_picker_callback: (callback, value, meta) => {
    if (meta.filetype !== 'image') {
      // Future: open DashboardMedia with type: 'document' | 'video'
      return;
    }
    window.DashboardMedia.open({
      multiple: false,
      type: 'image',
      onSelect: (item) => callback(item.url, {
        alt: item.alt || '',
        'data-media-id': String(item.id),
      }),
    });
  },
});
```

The Alpine Media Library modal in `dashboard-admin` listens for `open-media-library` and closes via `close-media-library`. Include the modal layout on any surface that hosts a rich-text editor.

## Future document / video

`App\Enums\MediaType` already defines `video`, `document`, and `audio`. Keep them disabled in `config/media.allowed_types` until dedicated upload pipelines and virus/content scanning exist. Private documents continue to use `MediaUploadService::storeDocument()` (always returns a path array).
