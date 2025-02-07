import { FontSizeConfig } from "./types/FontSizeConfig";

export class FontSizeProperty {
  static getConfig(): FontSizeConfig {
    return {
      fontSize: {
        options: [10, 12, 14, "default", 18, 20, 22],
        supportAllValues: true,
      },
    };
  }
}
