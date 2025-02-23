import { ReadOnlyModeConfig } from "./types/ReadOnlyModeConfig";

export class ReadOnlyModeProperty {
  static getConfig(isReadOnly: boolean): ReadOnlyModeConfig {
    return {
      readOnlyMode: {
        isReadOnly: isReadOnly,
      },
    };
  }
}
