import { HtmlSupportConfig } from "./types/HtmlSupportConfig";

export class HtmlSupportProperty {
  static getConfig(): HtmlSupportConfig {
    return {
      htmlSupport: {
        allow: [
          {
            name: /^.*$/,
            styles: true,
            attributes: true,
            classes: true,
          },
        ],
      },
    };
  }
}
