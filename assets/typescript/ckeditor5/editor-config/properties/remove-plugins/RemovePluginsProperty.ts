import { RemovePluginsConfig } from "./types/RemovePluginsConfig";

export class RemovePluginsProperty {
  static getConfig(): RemovePluginsConfig {
    return {
      removePlugins: ["MediaEmbedToolbar"],
    };
  }
}
