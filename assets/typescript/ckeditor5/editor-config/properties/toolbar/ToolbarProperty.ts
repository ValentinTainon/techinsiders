import { ToolbarConfig } from "./types/ToolbarConfig";

export class ToolbarProperty {
  static getConfig(
    toolbarItems: Array<string>,
    notGroupWhenFull: boolean = true
  ): ToolbarConfig {
    return {
      toolbar: {
        items: toolbarItems,
        shouldNotGroupWhenFull: notGroupWhenFull,
      },
    };
  }
}
