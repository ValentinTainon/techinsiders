import { FontFamilyConfig } from "./types/FontFamilyConfig";

export class FontFamilyProperty {
  static getConfig(): FontFamilyConfig {
    return {
      fontFamily: {
        supportAllValues: true,
      },
    };
  }
}
