import "ckeditor5/dist/ckeditor5.css";
import "../../styles/admin/ckeditor5/custom.css";
// @ts-ignore
import { ClassicEditor } from "ckeditor5";
import EditorConfigType from "./enum/EditorConfigType.ts";
import EditorStarterConfig from "./config/EditorStarterConfig.ts";
import EditorFeatureRichConfig from "./config/EditorFeatureRichConfig.ts";
import EditorWordCounter from "./EditorWordCounter.ts";
import EditorMediaCleaner from "./EditorMediaCleaner.ts";
import StarterConfigType from "./interface/StarterConfigType.ts";
import FeatureRichConfigType from "./interface/FeatureRichConfigType.ts";

export default class EditorInitializer {
  private editorWordCounter: EditorWordCounter;

  public async init(editorPlaceholder: HTMLTextAreaElement): Promise<void> {
    try {
      if (!editorPlaceholder) {
        throw new Error(
          "Cannot initialize editor due to missing editor placeholder."
        );
      }

      const editorDataset: DOMStringMap = editorPlaceholder.dataset;

      await ClassicEditor.create(
        editorPlaceholder,
        this.getEditorConfig(editorDataset)
      );

      if (
        editorDataset.editorConfigType === EditorConfigType.FeatureRich &&
        this.editorWordCounter
      ) {
        this.editorWordCounter.handlePostLengthValidation();
        new EditorMediaCleaner(editorDataset).cleanUnusedImages();
      }
    } catch (error) {
      alert(error);
      window.location.replace(
        window.location.href.split("/").slice(0, 6).join("/")
      );
    }
  }

  private getEditorConfig(
    editorDataset: DOMStringMap
  ): StarterConfigType | FeatureRichConfigType {
    switch (editorDataset.editorConfigType) {
      case EditorConfigType.Starter:
        return new EditorStarterConfig().getConfig();
      case EditorConfigType.FeatureRich:
        this.editorWordCounter = new EditorWordCounter(
          Number(editorDataset.minPostLengthLimit)
        );
        return new EditorFeatureRichConfig(
          editorDataset,
          this.editorWordCounter
        ).getConfig();
      default:
        console.warn(
          "Unknown editor config type, getting default starter config instead."
        );
        return new EditorStarterConfig().getConfig();
    }
  }
}
