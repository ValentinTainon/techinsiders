// @ts-ignore
import { ClassicEditor } from "ckeditor5";
import EditorConfigurator from "./EditorConfigurator.ts";
import EditorWordCounter from "./EditorWordCounter.ts";
import EditorMediaCleaner from "./EditorMediaCleaner.ts";

const editorPlaceholder: HTMLTextAreaElement | null =
  document.querySelector<HTMLTextAreaElement>("textarea#editor");

class EditorInitializer {
  private formTabsContent: HTMLDivElement | null;

  constructor() {
    this.formTabsContent =
      document.querySelector<HTMLDivElement>(".form-tabs-content");
  }

  public async init(): Promise<void> {
    try {
      if (!editorPlaceholder) {
        throw new Error(
          "Cannot initialize editor due to missing editor placeholder."
        );
      }

      const pageName: string = editorPlaceholder.dataset.pageName || "";
      const editorConfigType: string =
        editorPlaceholder.dataset.editorConfigType || "";
      const postUuid: string = editorPlaceholder.dataset.postUuid || "";
      const minPostLengthLimit: number =
        Number(editorPlaceholder.dataset.minPostLengthLimit) || 0;
      const editorWordCounter = new EditorWordCounter(minPostLengthLimit);

      await ClassicEditor.create(
        editorPlaceholder,
        new EditorConfigurator(postUuid, editorWordCounter).getConfig(
          editorConfigType
        )
      );

      editorWordCounter.handlePostLengthValidation();
      new EditorMediaCleaner(pageName, postUuid).cleanUnusedImages();
    } catch (error) {
      this.formTabsContent?.remove();
      console.error(error);
    }
  }
}

if (editorPlaceholder) {
  new EditorInitializer().init();
}
