import { TranslationsConfig } from "./types/TranslationsConfig";
// @ts-ignore
import FrTranslations from "ckeditor5/translations/fr.js";

export class TranslationsProperty {
  static getConfig(isFrLocale: boolean): TranslationsConfig {
    return {
      translations: isFrLocale ? FrTranslations : undefined,
    };
  }
}
