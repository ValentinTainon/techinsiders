import { ToolbarConfig } from "./types/ToolbarConfig";

export class ToolbarProperty {
  static getConfig(toolbarItems: Array<string>): ToolbarConfig {
    return {
      toolbar: {
        items: toolbarItems,
        shouldNotGroupWhenFull: true,
      },
    };
  }
}
