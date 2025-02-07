import { LicenseKeyConfig } from "./types/LicenseKeyConfig";

export class LicenseKeyProperty {
  static getConfig(): LicenseKeyConfig {
    return {
      licenseKey: "GPL",
    };
  }
}
