import { MenuBarConfig } from "./types/MenuBarConfig";

export class MenuBarProperty {
  static getConfig(): MenuBarConfig {
    return {
      menuBar: {
        isVisible: true,
      },
    };
  }
}
