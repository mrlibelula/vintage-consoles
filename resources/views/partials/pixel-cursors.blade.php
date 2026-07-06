@php
  $serverCursorStyle = auth()->check() ? (auth()->user()->cursor_style ?? 'alternate') : 'alternate';
@endphp

<script>
  (() => {
    const serverCursorStyle = @js($serverCursorStyle);
    const storedCursorStyle = localStorage.getItem('cursorStyle');
    const cursorStyle =
      storedCursorStyle === 'alternate' || storedCursorStyle === 'default'
        ? storedCursorStyle
        : serverCursorStyle;

    document.documentElement.dataset.cursorStyle = cursorStyle;

    if (storedCursorStyle !== cursorStyle) {
      localStorage.setItem('cursorStyle', cursorStyle);
    }
  })();
</script>

<style>
  /* Pixel Art Cursors (PNG) - use Laravel asset() so URLs are always https + correct host */
  body,
  iframe,
  canvas,
  #game,
  #game iframe,
  #game canvas,
  #dosbox,
  #dosbox canvas,
  .cursor-default {
    cursor: url('{{ asset('cursor-pointers/png/default.png') }}') 4 1, default;
  }

  html[data-cursor-style='alternate'] body,
  html[data-cursor-style='alternate'] iframe,
  html[data-cursor-style='alternate'] canvas,
  html[data-cursor-style='alternate'] #game,
  html[data-cursor-style='alternate'] #game iframe,
  html[data-cursor-style='alternate'] #game canvas,
  html[data-cursor-style='alternate'] #dosbox,
  html[data-cursor-style='alternate'] #dosbox canvas,
  html[data-cursor-style='alternate'] .cursor-default {
    cursor: url('{{ asset('cursor-pointers/png/default-alt.png') }}') 4 1, default;
  }

  a,
  button,
  [role='button'],
  summary,
  label[for],
  select,
  option,
  input[type='button'],
  input[type='submit'],
  input[type='reset'],
  .btn-small,
  .btn-pixel,
  #game a,
  #game button,
  #game [role='button'],
  #game [class*='ejs_'],
  #game [class^='ejs_'],
  #vintage-save-state-panel button,
  #vintage-save-state-panel [role='button'],
  .vintage-save-state-toggle,
  .vintage-save-state-close,
  .vintage-save-help-close,
  .vintage-save-upload-close,
  .vintage-save-confirm-close,
  #dosbox a,
  #dosbox button,
  #dosbox [role='button'],
  .cursor-pointer {
    cursor: url('{{ asset('cursor-pointers/png/pointer.png') }}') 12 1, pointer;
  }

  /*
   * SaveStateManager injects styles after load (e.g. `#vintage-save-state-panel button { cursor: pointer; }`),
   * so we need higher specificity in light mode to ensure our cursor wins.
   */
  html #vintage-save-state-panel button,
  html #vintage-save-state-panel [role='button'],
  html .vintage-save-state-toggle,
  html .vintage-save-state-close,
  html .vintage-save-help-close,
  html .vintage-save-upload-close,
  html .vintage-save-confirm-close {
    cursor: url('{{ asset('cursor-pointers/png/pointer.png') }}') 12 1, pointer;
  }

  input:not([type]),
  input[type='text'],
  input[type='search'],
  input[type='email'],
  input[type='url'],
  input[type='password'],
  input[type='number'],
  textarea,
  .cursor-text {
    cursor: url('{{ asset('cursor-pointers/png/text.png') }}') 16 16, text;
  }

  [draggable='true'],
  swiper-container[grab-cursor='true'],
  .screenshot-swiper[grab-cursor='true'],
  .screenshot-swiper-slide,
  swiper-slide,
  swiper-container[grab-cursor='true']::part(container),
  swiper-container[grab-cursor='true']::part(wrapper),
  swiper-container[grab-cursor='true']::part(slide),
  .cursor-grab {
    cursor: url('{{ asset('cursor-pointers/png/grab.png') }}') 16 16, grab;
  }

  [draggable='true']:active,
  swiper-container[grab-cursor='true']:active,
  swiper-slide:active,
  swiper-container[grab-cursor='true']::part(container):active,
  swiper-container[grab-cursor='true']::part(wrapper):active,
  swiper-container[grab-cursor='true']::part(slide):active,
  .cursor-grabbing {
    cursor: url('{{ asset('cursor-pointers/png/grabbing.png') }}') 16 16, grabbing;
  }

  :disabled,
  [aria-disabled='true'],
  .cursor-not-allowed {
    cursor: url('{{ asset('cursor-pointers/png/not-allowed.png') }}') 16 16, not-allowed;
  }

  .cursor-wait {
    cursor: url('{{ asset('cursor-pointers/png/wait.png') }}') 16 16, wait;
  }

  .cursor-crosshair {
    cursor: url('{{ asset('cursor-pointers/png/crosshair.png') }}') 16 16, crosshair;
  }

  .cursor-zoom-in {
    cursor: url('{{ asset('cursor-pointers/png/zoom-in.png') }}') 16 16, zoom-in;
  }

  .cursor-zoom-out {
    cursor: url('{{ asset('cursor-pointers/png/zoom-out.png') }}') 16 16, zoom-out;
  }

  .cursor-col-resize {
    cursor: url('{{ asset('cursor-pointers/png/col-resize.png') }}') 16 16, col-resize;
  }

  .cursor-row-resize {
    cursor: url('{{ asset('cursor-pointers/png/row-resize.png') }}') 16 16, row-resize;
  }

  .cursor-ew-resize {
    cursor: url('{{ asset('cursor-pointers/png/ew-resize.png') }}') 16 16, ew-resize;
  }

  .cursor-ns-resize {
    cursor: url('{{ asset('cursor-pointers/png/ns-resize.png') }}') 16 16, ns-resize;
  }

  .cursor-ne-resize {
    cursor: url('{{ asset('cursor-pointers/png/ne-resize.png') }}') 16 16, ne-resize;
  }

  .cursor-nw-resize {
    cursor: url('{{ asset('cursor-pointers/png/nw-resize.png') }}') 16 16, nw-resize;
  }

  .cursor-se-resize {
    cursor: url('{{ asset('cursor-pointers/png/se-resize.png') }}') 16 16, se-resize;
  }

  .cursor-sw-resize {
    cursor: url('{{ asset('cursor-pointers/png/sw-resize.png') }}') 16 16, sw-resize;
  }

  .cursor-nesw-resize {
    cursor: url('{{ asset('cursor-pointers/png/nesw-resize.png') }}') 16 16, nesw-resize;
  }

  .cursor-nwse-resize {
    cursor: url('{{ asset('cursor-pointers/png/nwse-resize.png') }}') 16 16, nwse-resize;
  }

  html.dark body,
  html.dark iframe,
  html.dark canvas,
  html.dark #game,
  html.dark #game iframe,
  html.dark #game canvas,
  html.dark #dosbox,
  html.dark #dosbox canvas,
  html.dark .cursor-default {
    cursor: url('{{ asset('cursor-pointers/png/default-dark.png') }}') 4 1, default;
  }

  html.dark[data-cursor-style='alternate'] body,
  html.dark[data-cursor-style='alternate'] iframe,
  html.dark[data-cursor-style='alternate'] canvas,
  html.dark[data-cursor-style='alternate'] #game,
  html.dark[data-cursor-style='alternate'] #game iframe,
  html.dark[data-cursor-style='alternate'] #game canvas,
  html.dark[data-cursor-style='alternate'] #dosbox,
  html.dark[data-cursor-style='alternate'] #dosbox canvas,
  html.dark[data-cursor-style='alternate'] .cursor-default {
    cursor: url('{{ asset('cursor-pointers/png/default-alt-dark.png') }}') 4 1, default;
  }

  html.dark a,
  html.dark button,
  html.dark [role='button'],
  html.dark summary,
  html.dark label[for],
  html.dark select,
  html.dark option,
  html.dark input[type='button'],
  html.dark input[type='submit'],
  html.dark input[type='reset'],
  html.dark .btn-small,
  html.dark .btn-pixel,
  html.dark #game a,
  html.dark #game button,
  html.dark #game [role='button'],
  html.dark #game [class*='ejs_'],
  html.dark #game [class^='ejs_'],
  html.dark #vintage-save-state-panel button,
  html.dark #vintage-save-state-panel [role='button'],
  html.dark .vintage-save-state-toggle,
  html.dark .vintage-save-state-close,
  html.dark .vintage-save-help-close,
  html.dark .vintage-save-upload-close,
  html.dark .vintage-save-confirm-close,
  html.dark #dosbox a,
  html.dark #dosbox button,
  html.dark #dosbox [role='button'],
  html.dark .cursor-pointer {
    cursor: url('{{ asset('cursor-pointers/png/pointer-dark.png') }}') 12 1, pointer;
  }

  html.dark #vintage-save-state-panel button,
  html.dark #vintage-save-state-panel [role='button'],
  html.dark .vintage-save-state-toggle,
  html.dark .vintage-save-state-close,
  html.dark .vintage-save-help-close,
  html.dark .vintage-save-upload-close,
  html.dark .vintage-save-confirm-close {
    cursor: url('{{ asset('cursor-pointers/png/pointer-dark.png') }}') 12 1, pointer;
  }

  html.dark [draggable='true'],
  html.dark swiper-container[grab-cursor='true'],
  html.dark .screenshot-swiper[grab-cursor='true'],
  html.dark .screenshot-swiper-slide,
  html.dark swiper-slide,
  html.dark swiper-container[grab-cursor='true']::part(container),
  html.dark swiper-container[grab-cursor='true']::part(wrapper),
  html.dark swiper-container[grab-cursor='true']::part(slide),
  html.dark .cursor-grab {
    cursor: url('{{ asset('cursor-pointers/png/grab-dark.png') }}') 16 16, grab;
  }

  html.dark [draggable='true']:active,
  html.dark swiper-container[grab-cursor='true']:active,
  html.dark swiper-slide:active,
  html.dark swiper-container[grab-cursor='true']::part(container):active,
  html.dark swiper-container[grab-cursor='true']::part(wrapper):active,
  html.dark swiper-container[grab-cursor='true']::part(slide):active,
  html.dark .cursor-grabbing {
    cursor: url('{{ asset('cursor-pointers/png/grabbing-dark.png') }}') 16 16, grabbing;
  }

  html.dark :disabled,
  html.dark [aria-disabled='true'],
  html.dark .cursor-not-allowed {
    cursor: url('{{ asset('cursor-pointers/png/not-allowed-dark.png') }}') 16 16, not-allowed;
  }

  html.dark .cursor-wait {
    cursor: url('{{ asset('cursor-pointers/png/wait-dark.png') }}') 16 16, wait;
  }

  html.dark input:not([type]),
  html.dark input[type='text'],
  html.dark input[type='search'],
  html.dark input[type='email'],
  html.dark input[type='url'],
  html.dark input[type='password'],
  html.dark input[type='number'],
  html.dark textarea,
  html.dark .cursor-text {
    cursor: url('{{ asset('cursor-pointers/png/text.png') }}') 16 16, text;
  }
</style>
