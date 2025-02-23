// @ts-ignore
import { Plugin } from "ckeditor5";

export class ReadOnlyMode extends Plugin {
  public static get pluginName() {
    return "ReadOnlyMode" as const;
  }

  public init(): void {
    // @ts-ignore
    const editor = this.editor;
    const readOnlyModeConfig = editor.config.get("readOnlyMode");
    const isReadOnly: boolean = readOnlyModeConfig.isReadOnly;

    if (isReadOnly) {
      editor.once("ready", () => {
        editor.enableReadOnlyMode("editor-locked");
        const toolbarElement = editor.ui.view.toolbar.element;
        if (toolbarElement) toolbarElement.style.display = "none";
      });
    }
  }
}
