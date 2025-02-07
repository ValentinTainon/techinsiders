import { LanguageConfig } from "./types/LanguageConfig";
import {
  trans,
  PLUGIN_LANGUAGE_FRENCH,
  PLUGIN_LANGUAGE_ENGLISH,
} from "../../../../translator.ts";

export class LanguageProperty {
  static getConfig(isFrLocale: boolean): LanguageConfig {
    return {
      language: {
        ui: isFrLocale ? "fr" : "en",
        content: isFrLocale ? "fr" : "en",
        textPartLanguage: [
          {
            title: trans(PLUGIN_LANGUAGE_FRENCH, {}, "ckeditor5"),
            languageCode: "fr",
          },
          {
            title: trans(PLUGIN_LANGUAGE_ENGLISH, {}, "ckeditor5"),
            languageCode: "en",
          },
        ],
      },
    };
  }
}
