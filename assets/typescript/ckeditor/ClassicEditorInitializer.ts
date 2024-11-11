// @ts-ignore
import { ClassicEditor } from "ckeditor5";
import ClassicEditorConfig from "./ClassicEditorConfig.ts";
import CkWordCountUpdater from "./CkWordCountUpdater.ts";

class ClassicEditorInitializer {
  private ckeditorField: HTMLDivElement | null;
  private ckeditorPlaceholder: HTMLTextAreaElement | null;
  private formTabsContent: HTMLDivElement | null;

  constructor() {
    this.ckeditorField =
      document.querySelector<HTMLDivElement>("div.field-ckeditor");
    this.ckeditorPlaceholder =
      document.querySelector<HTMLTextAreaElement>("textarea#editor");
    this.formTabsContent =
      document.querySelector<HTMLDivElement>(".form-tabs-content");
  }

  public async init(): Promise<void> {
    try {
      if (!this.ckeditorField) {
        throw new Error(
          "Cannot initialize editor due to missing editor field."
        );
      }
      if (!this.ckeditorPlaceholder) {
        throw new Error(
          "Cannot initialize editor due to missing editor placeholder."
        );
      }

      const ckeditorConfigType: string =
        this.ckeditorField.dataset.editorConfigType || "";
      const minPostLengthLimit: number =
        Number(this.ckeditorField.dataset.minPostLengthLimit) || 0;
      const ckWordCountUpdater = new CkWordCountUpdater(minPostLengthLimit);

      await ClassicEditor.create(
        this.ckeditorPlaceholder,
        new ClassicEditorConfig(ckeditorConfigType, ckWordCountUpdater).config()
      );

      ckWordCountUpdater.handlePostLengthValidation();
    } catch (error) {
      this.formTabsContent?.remove();
      console.error(error);
    }
  }
}

new ClassicEditorInitializer().init();
