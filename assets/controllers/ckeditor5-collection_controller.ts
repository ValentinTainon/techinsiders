// @ts-ignore
import { Controller } from "@hotwired/stimulus";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  // CKEditor5 Collection Field
  declare element: HTMLDivElement;

  connect(): void {
    const ckeditor5CollectionField = this.element;

    const collectionItems: NodeListOf<HTMLDivElement> =
      ckeditor5CollectionField.querySelectorAll<HTMLDivElement>(
        "div.field-collection-item"
      );

    if (collectionItems.length > 0) {
      this.removeDeleteButtonIfReadOnlyItems(collectionItems);
    }
  }

  private removeDeleteButtonIfReadOnlyItems(
    collectionItems: NodeListOf<HTMLDivElement>
  ): void {
    collectionItems.forEach((item) => {
      const itemReadOnly = item.querySelector<HTMLTextAreaElement>(
        "textarea[data-read-only='true']"
      );

      if (!itemReadOnly) return;

      const deleteButton = item.querySelector<HTMLButtonElement>(
        "button.field-collection-delete-button"
      );

      if (deleteButton) deleteButton.remove();
    });
  }
}
