import { ListConfig } from "./types/ListConfig";

export class ListProperty {
  static getConfig(): ListConfig {
    return {
      list: {
        properties: {
          styles: true,
          startIndex: true,
          reversed: true,
        },
      },
    };
  }
}
