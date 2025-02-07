import { PlaceholderConfig } from "./types/PlaceholderConfig";
import { trans, PLACEHOLDER } from "../../../../translator.ts";

export class PlaceholderProperty {
  static getConfig(): PlaceholderConfig {
    return {
      placeholder: trans(PLACEHOLDER, {}, "ckeditor5"),
    };
  }
}
