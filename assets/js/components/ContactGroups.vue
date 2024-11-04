<template>
  <div class="contact-groups-component">
    <div class="contact-groups-component-title">
      <h2>Kontaktgruppen</h2>

      <transition name="fade" mode="out-in">
        <div class="loading-indicator" v-if="isLoading('contactGroups')"></div>
      </transition>

      <div class="contact-groups-component-title-actions">
        <router-link :to="'/contact-groups/add'" class="button primary"
          >Neuen Eintrag erstellen</router-link
        >
      </div>
    </div>

    <div class="contact-groups-component-filter">
      <div class="row">
        <div class="col-md-8">
          <div class="form-group">
            <label for="term">Suchbegriff</label>
            <input
              id="term"
              type="text"
              class="form-control"
              v-model="term"
              @change="changeForm()"
            />
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label for="status">Status</label>
            <div class="select-wrapper">
              <select
                id="status"
                class="form-control"
                @change="
                  addFilter({ type: 'status', value: $event.target.value });
                  $event.target.value = null;
                "
              >
                <option></option>
                <option :value="'public'">Öffentlich</option>
                <option :value="'draft'">Entwurf</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="contacts-component-filter-tags">
        <div
          class="tag"
          v-for="filter of filters"
          @click="removeFilter({ type: filter.type, value: filter.value })"
        >
          <strong v-if="filter.type === 'status'">Status:</strong>
          <template v-if="['status'].includes(filter.type)">
            &nbsp;{{ filter.value === "public" ? "Öffentlich" : "Entwurf" }}
          </template>
          <template v-else>{{ filter.value }}</template>
        </div>
      </div>
    </div>

    <div class="contact-groups-component-content">
      <div class="contact-groups-component-tree-structure">
        <draggable
          :list="contactGroupsElements"
          :tag="'ul'"
          class="contact-groups-component-tree-structure-level-parent"
          item-key="position"
          @change="changeSort"
          :disabled="!isDraggableEnabled"
        >
          <template #item="{ element, index }">
            <li
              :class="{
                draft: !element.group.isPublic,
                clickable: isDraggableEnabled,
              }"
            >
              <div class="contact-groups-component-tree-structure-container">
                <h2
                  class="contact-groups-component-tree-structure-container-label"
                  @click="clickContactGroup(element.group)"
                >
                  <span class="material-icons">folder</span>
                  {{ element.group.name }}
                </h2>

                <div class="contact-groups-component-tree-structure-container-actions">
                  <a
                    class="button"
                    :href="getExportURL(element.group.id)"
                    download
                    v-if="
                      getContactSubGroups(element.group.id)?.length &&
                      selectedElements[element.group.id]?.length
                    "
                    ><span class="material-icons">file_download</span></a
                  >
                  <a
                    class="button"
                    @click="openEmailModal(element.group.id)"
                    v-if="
                      getContactSubGroups(element.group.id)?.length &&
                      selectedElements[element.group.id]?.length
                    "
                  >
                    <span
                      class="material-icons"
                      title="Sende Verifizierungsanfrage der Kontaktdaten"
                      >email</span
                    >
                  </a>
                  <span
                    class="material-icons"
                    @click.stop="clickContactGroup(element.group)"
                    >edit</span
                  >
                  <span class="material-icons" v-if="!element.group.isPublic"
                    >visibility_off</span
                  >
                  <span class="material-icons" v-else>visibility</span>
                </div>
              </div>

              <draggable
                :list="element.children"
                :tag="'ul'"
                class="contact-groups-component-tree-structure-level-child"
                item-key="id"
                @change="changeSort"
                v-if="element.children?.length"
                :disabled="!isDraggableEnabled"
              >
                <template #item="{ element: childElement, index: childIndex }">
                  <li :class="{ clickable: isDraggableEnabled }">
                    <div class="contact-groups-component-tree-structure-container child">
                      <h3
                        class="contact-groups-component-tree-structure-container-label"
                        @click="clickContactGroup(childElement)"
                      >
                        <span class="material-icons">groups</span>
                        {{ childElement.name }}
                      </h3>

                      <div
                        class="contact-groups-component-tree-structure-container-contact-actions"
                      >
                        <input
                          id="active"
                          type="checkbox"
                          :class="{ disabled: !childElement.contacts?.length }"
                          :checked="
                            selectedElements[element.group.id]?.find(
                              (group) => group.id === childElement.id
                            )
                          "
                          @change="
                            clickToggleSelected(
                              $event,
                              childElement,
                              element.group.id,
                              childElement.id
                            )
                          "
                        />
                        <span
                          class="material-icons"
                          @click.stop="clickContactGroup(childElement)"
                          >edit</span
                        >
                        <span class="material-icons" v-if="!childElement.isPublic"
                          >visibility_off</span
                        >
                        <span class="material-icons" v-else>visibility</span>
                        <i v-if="childElement.contacts?.length"
                          >[ Mitglieder: {{ childElement.contacts.length }} ]
                        </i>
                        <i v-else>[ Keine Mitglieder ]</i>
                      </div>
                    </div>
                  </li>
                </template>
              </draggable>
            </li>
          </template>
        </draggable>
      </div>
    </div>

    <transition name="fade" mode="in-out">
      <div class="context-bar" v-if="isSortChanged">
        <div class="context-bar-content">
          <p v-if="!isLoading('contactGroups/*')">
            Sortierung geändert. Möchten Sie die Änderungen speichern?
          </p>
          <p v-else>
            {{ sortChangeProgress }} von {{ contactGroupsAll.length }} Positionen
            gespeichert...
          </p>
        </div>
        <template v-if="!isLoading('contactGroups/*')">
          <a class="button warning" @click="clickRestoreSort()">Zurücksetzen</a>
          <a class="button success" @click="clickSaveSort()">Speichern</a>
        </template>
      </div>
    </transition>

    <!-- Email Modal -->
    <transition name="modal">
      <div class="modal-mask" v-if="isEmailModalOpen">
        <div class="modal-wrapper">
          <div class="modal-container">
            <div class="modal-header">
              <h3>Kontaktdaten Validierungsanfrage - Rundmail</h3>
              <p style="font-size: 0.8em; font-style: italic">
                Wenn Sie auf "E-Mails senden" klicken, wird ein
                <span style="font-weight: 700">Verifizierungslink</span> für jeden Kontakt
                versendet. Der Link führt zu einem Formular zur einmaligen Validierung der
                Kontaktdaten der jeweiligen Kontaktperson.
              </p>
            </div>

            <div class="modal-body">
              <div class="email-preview">
                <p>Sehr geehrte/r [Name],</p>
                <p>
                  Bitte verifizieren Sie Ihre Kontaktdaten, indem Sie auf den folgenden
                  Link klicken.
                </p>
                <p>[Verifizierungslink]</p>
                <p>Mit freundlichen Grüßen,</p>
                <p>Ihr regiosuisse Team</p>
              </div>
              <table id="send-email-table">
                <th>Empfänger</th>
                <th>Name</th>
                <th>Zuletzt gesendet</th>
                <th></th>
                <tr
                  v-for="(receiver, index) in emailModalData.receivers"
                  :key="receiver.id"
                >
                  <td>{{ getContactById(receiver.id).email }}</td>
                  <td>
                    {{ getContactById(receiver.id).firstName }}
                    {{ getContactById(receiver.id).lastName }}
                  </td>
                  <td>
                    {{
                      getContactById(receiver.id).verificationEmailSentDate
                        ? formatDateTime(
                            getContactById(receiver.id).verificationEmailSentDate
                          )
                        : "Nicht gesendet"
                    }}
                    <span
                      v-if="
                        showDaysAgo(getContactById(receiver.id).verificationEmailSentDate)
                      "
                      style="color: red"
                    >
                      (vor
                      {{
                        getDaysAgo(getContactById(receiver.id).verificationEmailSentDate)
                      }}
                      Tagen)
                    </span>
                  </td>
                  <td>
                    <button class="button error" @click="removeReceiver(index)">
                      Entfernen
                    </button>
                  </td>
                </tr>
              </table>
            </div>

            <div class="modal-footer">
              <button
                class="button success modal-default-button"
                @click="openConfirmationModal"
              >
                E-Mails senden
              </button>
              <button class="button error modal-default-button" @click="closeEmailModal">
                Abbrechen
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
    <!-- End of Email Modal -->

    <!-- Confirmation Modal -->
    <transition name="modal">
      <div class="modal-mask" v-if="isConfirmationModalOpen">
        <div class="modal-wrapper">
          <div class="modal-container">
            <div class="modal-header">
              <h3>Bestätigung der E-Mail-Sendung</h3>
            </div>

            <div class="modal-body">
              <p>
                Möchten Sie diese E-Mail wirklich an
                <span style="font-weight: 700">{{
                  emailModalData.receivers.length
                }}</span>
                Empfänger senden?
              </p>
            </div>

            <div class="modal-footer">
              <button class="button success modal-default-button" @click="sendEmails">
                Bestätigen
              </button>
              <button
                class="button error modal-default-button"
                @click="closeConfirmationModal"
              >
                Abbrechen
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import { mapGetters, mapState } from "vuex";
import moment from "moment";
import draggable from "vuedraggable";
import axios from "axios";

export default {
  data() {
    return {
      position: 10000,
      term: "",
      filters: [],
      selectedElements: {},
      isSortChanged: false,
      sortChangeProgress: 0,
      contactGroupsElements: [],
      isEmailModalOpen: false,
      isConfirmationModalOpen: false,
      emailModalData: {
        groupId: null,
        receivers: [],
      },
    };
  },
  components: {
    draggable,
  },
  computed: {
    ...mapState({
      contactGroups: (state) => state.contactGroups.filtered,
      contactGroupsAll: (state) => state.contactGroups.all,
      contacts: (state) => state.contacts.all,
    }),
    ...mapGetters({
      isLoading: "loaders/isLoading",
      getContactById: "contacts/getById",
    }),
    isDraggableEnabled() {
      return !this.filters?.length && !this.term;
    },
  },
  methods: {
    sortByPosition(a, b) {
      let contactGroupA = a.position;
      let contactGroupB = b.position;
      return contactGroupA < contactGroupB ? -1 : contactGroupA > contactGroupB ? 1 : 0;
    },
    changeForm() {
      this.saveFilter();
      this.reloadContactGroups();
    },
    getFilterParams() {
      let params = {};
      params.term = this.term;

      this.filters.forEach((filter) => {
        if (!params[filter.type]) {
          params[filter.type] = [];
        }
        params[filter.type].push(filter.value);
      });

      params.orderBy = ["position", "name"];
      params.orderDirection = ["ASC", "ASC"];

      return params;
    },
    reloadContactGroups() {
      this.isSortChanged = false;

      this.$store
        .dispatch("contactGroups/loadFiltered", this.getFilterParams())
        .then((contactGroups) => {
          this.contactGroupsElements = [];
          let contactGroupsParents = contactGroups.filter((group) => !group.parent);

          for (let contactGroupParent of contactGroupsParents) {
            let contactGroupsChildren = this.getContactSubGroups(contactGroupParent.id);

            this.contactGroupsElements.push({
              group: contactGroupParent,
              children: contactGroupsChildren,
            });
          }
        });
    },
    getContactSubGroups(contactGroupId) {
      return (
        this.contactGroupsAll
          .filter((group) => group.parent?.id === contactGroupId)
          ?.sort(this.sortByPosition) || []
      );
    },
    clickToggleSelected(event, element, groupIndex, subgroupIndex) {
      let isSelected = event.target.checked;

      delete element.activeContactGroup;

      if (!this.selectedElements[groupIndex]) {
        this.selectedElements[groupIndex] = [];
      }

      if (!isSelected) {
        let elementIndex = this.selectedElements[groupIndex].indexOf(element);

        if (elementIndex !== null) {
          this.selectedElements[groupIndex].splice(elementIndex, 1);
        }

        return;
      }

      element.activeContactGroup = subgroupIndex;

      this.selectedElements[groupIndex].push(element);
    },
    validateSelectedElements(groupIndex) {
      if (!this.selectedElements[groupIndex]?.length) {
        alert("Wählen Sie eine oder mehrere Kontaktgruppen zur weiteren Bearbeitung.");
        return false;
      }

      return true;
    },
    clickContact(contact) {
      this.$router.push({
        path: "/contacts/" + contact.type + "/" + contact.id + "/edit",
      });
    },
    clickContactGroup(contactGroup) {
      this.$router.push({
        path: "/contact-groups/" + contactGroup.id + "/edit",
      });
    },
    getExportURL(groupIndex) {
      if (!this.validateSelectedElements(groupIndex)) {
        return;
      }

      if (!this.selectedElements[groupIndex]?.length) {
        return;
      }

      let filterParams = [];

      for (let element of this.selectedElements[groupIndex]) {
        if (element.contacts?.length) {
          for (let contact of element.contacts) {
            if (
              !filterParams.includes("contactGroupIds[]=" + element.activeContactGroup)
            ) {
              filterParams.push("contactGroupIds[]=" + element.activeContactGroup);
            }
          }
        }
      }

      return "/api/v1/contacts.xlsx/contact-groups?" + filterParams.join("&");
    },
    formatOneToMany(items, getter) {
      let result = [];
      items.forEach((item) => {
        result.push(getter(item.id)?.name);
      });

      return result.join(", ");
    },
    formatDate(date, format = "DD.MM.YYYY") {
      if (date && moment(date)) {
        return moment(date).format(format);
      }
    },
    formatDateTime(date) {
      return this.formatDate(date, "DD.MM.YYYY HH:mm");
    },
    addFilter(filter) {
      if (!filter.value) {
        return;
      }
      if (
        this.filters
          .filter((f) => f.type === filter.type)
          .find((f) => f.value === filter.value)
      ) {
        return;
      }
      this.filters.push(filter);
      this.changeForm();
    },
    removeFilter(filter) {
      let f = this.filters
        .filter((f) => f.type === filter.type)
        .find((f) => f.value === filter.value);
      if (f) {
        this.filters.splice(this.filters.indexOf(f), 1);
      }
      this.changeForm();
    },
    saveFilter() {
      window.sessionStorage.setItem(
        "regiosuisse.contactGroups.filters",
        JSON.stringify(this.filters)
      );
      window.sessionStorage.setItem("regiosuisse.contactGroups.term", this.term);

      this.selectedElements = {};
    },
    loadFilter() {
      this.filters = JSON.parse(
        window.sessionStorage.getItem("regiosuisse.contactGroups.filters") || "[]"
      );
      this.term = window.sessionStorage.getItem("regiosuisse.contactGroups.term") || "";
    },
    changeSort() {
      this.isSortChanged = true;
    },
    async clickSaveSort() {
      this.sortChangeProgress = 0;

      for (let key in this.contactGroupsElements) {
        await this.$store.dispatch("contactGroups/update", {
          ...this.contactGroupsElements[key].group,
          position: key,
        });
        this.sortChangeProgress++;

        for (let childKey in this.contactGroupsElements[key].children) {
          await this.$store.dispatch("contactGroups/update", {
            ...this.contactGroupsElements[key].children[childKey],
            position: childKey,
          });
          this.sortChangeProgress++;
        }
      }

      this.isSortChanged = false;

      this.reloadContactGroups();
    },
    clickRestoreSort() {
      this.isSortChanged = false;
      this.reloadContactGroups();
    },
    // New methods for email modal
    openEmailModal(groupId) {
      if (!this.validateSelectedElements(groupId)) {
        return;
      }
      this.emailModalData.groupId = groupId;
      this.emailModalData.receivers = this.getSelectedReceivers(groupId);
      this.isEmailModalOpen = true;
    },
    // Open the confirmation modal when the "E-Mails senden" button is clicked
    openConfirmationModal() {
      this.isConfirmationModalOpen = true;
    },

    // Close the confirmation modal
    closeConfirmationModal() {
      this.isConfirmationModalOpen = false;
    },

    getSelectedReceivers(groupId) {
      let receivers = [];
      for (let element of this.selectedElements[groupId]) {
        if (element.contacts?.length) {
          for (let contact of element.contacts) {
            if (contact.id) {
              receivers.push(contact);
            }
          }
        }
      }

      // Filter out duplicate contacts by their email or ID
      const uniqueReceivers = Array.from(
        new Set(receivers.map((receiver) => receiver.id))
      ).map((id) => receivers.find((receiver) => receiver.id === id));

      return uniqueReceivers;
    },
    closeEmailModal() {
      this.isEmailModalOpen = false;
    },
    removeReceiver(index) {
      this.emailModalData.receivers.splice(index, 1);
    },
    sendEmails() {
      let receiverEmails = [
        ...new Set(
          this.emailModalData.receivers
            .filter((receiver) => receiver.email)
            .map((receiver) => receiver.id)
        ),
      ];

      axios
        .post("/api/v1/contacts/send-verification-emails", {
          emails: receiverEmails,
        })
        .then((response) => {
          alert("E-Mails wurden erfolgreich gesendet.");
          this.closeEmailModal();
          this.closeConfirmationModal();
        })
        .catch((error) => {
          alert("Beim Senden der E-Mails ist ein Fehler aufgetreten.");
        });
    },
    getDaysAgo(date) {
      if (!date) return 0;
      return moment().diff(moment(date), "days");
    },

    // Diese Methode prüft, ob das Datum innerhalb eines Monats zurückliegt
    showDaysAgo(date) {
      if (!date) return false;
      const daysAgo = this.getDaysAgo(date);
      return daysAgo <= 30;
    },
  },
  created() {
    this.loadFilter();
    this.reloadContactGroups();
    this.$store.dispatch("contacts/loadAll");
  },
};
</script>

<style lang="scss" scoped>
/* Add styles for modal */
.modal-mask {
  position: fixed;
  z-index: 9998;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: table;
  width: 100%;
  height: 100%;
  transition: opacity 0.3s ease;
}

.modal-wrapper {
  display: table-cell;
  vertical-align: middle;
}

.modal-container {
  width: 1000px;
  margin: 0px auto;
  padding: 20px 30px;
  background-color: #fff;
  border-radius: 2px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
  transition: all 0.3s ease;
  font-family: Helvetica, Arial, sans-serif;
}

.modal-header h3 {
  margin-top: 0;
  color: #b4be00;
}

.modal-body {
  margin: 20px 0;
}

.modal-default-button {
  float: right;
  margin-left: 10px;
}

.modal-enter {
  opacity: 0;
}

.modal-leave-active {
  opacity: 0;
}

.modal-footer {
  padding-bottom: 20px;
}

.modal-footer::after {
  content: "";
  clear: both;
  display: block;
}

.modal-enter .modal-container,
.modal-leave-active .modal-container {
  transform: scale(1.1);
}

.email-preview {
  border: 1px solid #ccc;
  padding: 10px;
  margin-bottom: 10px;
  background-color: white;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
}

table th,
table td {
  border: 1px solid #ddd;
  padding: 4px;
  text-align: left;
}

table th {
  background-color: #f2f2f2;
  font-weight: bold;
}

table tr:nth-child(even) {
  background-color: #f9f9f9;
}

table tr:hover {
  background-color: #f1f1f1;
}

table th,
table td {
  padding: 12px 15px;
}

table th {
  background-color: #b4be00;
  color: white;
}
</style>
