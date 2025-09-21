// src/directives/clickOutside.ts
import { DirectiveBinding } from 'vue';

type Handler = (e: MouseEvent) => void;

export default {
  beforeMount(el: HTMLElement, binding: DirectiveBinding<Handler>) {
    const handler = (e: MouseEvent) => {
      if (!el.contains(e.target as Node)) {
        binding.value(e);
      }
    };
    // store on element so we can remove later
    (el as any).__clickOutsideHandler__ = handler;
    document.addEventListener('click', handler);
  },
  unmounted(el: HTMLElement) {
    const handler = (el as any).__clickOutsideHandler__;
    if (handler) document.removeEventListener('click', handler);
    delete (el as any).__clickOutsideHandler__;
  }
};
