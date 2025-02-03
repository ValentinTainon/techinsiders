// @ts-ignore
import { Controller } from "@hotwired/stimulus";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  connect(): void {
    const postCommentsCollectionItems: NodeListOf<HTMLDivElement> =
      document.querySelectorAll<HTMLDivElement>("div.field-collection-item");

    if (postCommentsCollectionItems.length > 0) {
      this.removeAllowDeleteIfUnauthorizedUser(postCommentsCollectionItems);
    }
  }

  private removeAllowDeleteIfUnauthorizedUser(
    postCommentsCollectionItems: NodeListOf<HTMLDivElement>
  ): void {
    postCommentsCollectionItems.forEach((item) => {
      const itemWithDataAllowDelete = item.querySelector<HTMLDivElement>(
        "div[data-allow-delete-item]"
      );

      if (!itemWithDataAllowDelete) return;

      const deleteButton = item.querySelector<HTMLButtonElement>(
        "button.field-collection-delete-button"
      );

      if (!deleteButton) return;

      if (itemWithDataAllowDelete.dataset.allowDeleteItem === "false") {
        deleteButton.remove();
      }
    });
  }
}
