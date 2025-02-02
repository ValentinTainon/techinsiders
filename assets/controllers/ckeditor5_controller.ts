// @ts-ignore
import { Controller } from "@hotwired/stimulus";
import EditorInitializer from "../typescript/ckeditor5/EditorInitializer.ts";

export default class extends Controller {
  connect(): void {
    const editorPlaceholder: HTMLTextAreaElement | null =
      document.querySelector<HTMLTextAreaElement>("textarea#editor");

    if (editorPlaceholder) {
      new EditorInitializer().init(editorPlaceholder);
    }
  }
}
