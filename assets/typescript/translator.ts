// @ts-ignore
import { localeFallbacks } from "@app/translations/configuration";
import {
  trans,
  getLocale,
  setLocale,
  setLocaleFallbacks,
  throwWhenNotFound,
  // @ts-ignore
} from "@symfony/ux-translator";
/*
 * This file is part of the Symfony UX Translator package.
 *
 * If folder "../var/translations" does not exist, or some translations are missing,
 * you must warmup your Symfony cache to refresh JavaScript translations.
 */

setLocaleFallbacks(localeFallbacks);
throwWhenNotFound(true);

export { trans };

// @ts-ignore
export * from "@app/translations";
