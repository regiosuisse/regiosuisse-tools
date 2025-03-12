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
                  <input
                      id="active"
                      type="checkbox"
                      :class="{ disabled: !element.group.contacts?.length }"
                      :checked="
                          selectedElements[element.group.id]?.find(
                            (group) => group.id === element.group.id
                          )
                        "
                      @change="
                          clickToggleSelected(
                            $event,
                            element.group,
                            element.group.id,
                            element.group.id
                          )
                        "
                  />
                  <span
                    class="material-icons"
                    @click.stop="clickContactGroup(element.group)"
                    >edit</span
                  >
                  <span class="material-icons" v-if="!element.group.isPublic"
                    >visibility_off</span
                  >
                  <span class="material-icons" v-else>visibility</span>
                    <i v-if="element.group.contacts?.length"
                    >[ Mitglieder: {{ element.group.contacts.length }} ]
                    </i>
                    <i v-else>[ Keine Mitglieder ]</i>
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
              <div class="email-container">
                <div class="email-header">
                    <p>
                        LOGO
                    </p>
                    <p><h3>Aktualisierung Ihrer Daten in unserer regiosuisse Expertinnen- und Expertendatenbank</h3></p>
                </div>

                <!-- Email content -->
                <div class="email-content">
                    <p>Sehr geehrte Expertin, sehr geehrter Experte,</p>
                    <p>Wir möchten sicherstellen, dass die Informationen in unserer regiosuisse Expertinnen- und Expertendatenbank aktuell und korrekt sind. Bitte nehmen Sie sich einen Moment Zeit, um Ihre Daten über folgenden Link zu überprüfen und gegebenenfalls anzupassen: <a href="#">Meine Kontaktdaten überprüfen und aktualisieren</a></p>
                    <p>Sollten Sie wünschen, dass Ihre Daten aus unserer Datenbank gelöscht werden, können Sie dies ebenfalls über den oben genannten Link veranlassen.</p>
                    <p>Vielen Dank für Ihre Mithilfe!</p>
                </div>

                <!-- Footer -->
                <div class="email-footer">
                    <p>Herzliche Grüsse,</p>
                    <p>Ihr regiosuisse-Team</p>
                </div>
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
    openEmailModal(groupId) {
      if (!this.validateSelectedElements(groupId)) {
        return;
      }
      this.emailModalData.groupId = groupId;
      this.emailModalData.receivers = this.getSelectedReceivers(groupId);
      this.isEmailModalOpen = true;
    },
    openConfirmationModal() {
      this.isConfirmationModalOpen = true;
    },
    closeConfirmationModal() {
      this.isConfirmationModalOpen = false;
    },
    getSelectedReceivers(groupId) {
      let receivers = [];
      for (let element of this.selectedElements[groupId]) {
        if (element.contacts?.length) {
          for (let contact of element.contacts) {
            if (contact.id) {
              let currentContact = this.getContactById(contact.id);
              // only select persons not companies
              if (currentContact.type === "person") {
                receivers.push(currentContact);
              }
            }
          }
        }
      }
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
        ...new Set(this.emailModalData.receivers.map((receiver) => receiver.id)),
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
.modal-mask {
  position: fixed;
  z-index: 9998;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.3s ease;
}

.modal-wrapper {
  background: #fff;
  width: 90%;
  max-width:50vw;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
}

.modal-header {
  padding: 16px;
  background: #f5f5f5;
  flex: 0 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: start;
  border-bottom: 1px solid #ddd;
  flex-direction: column;

  h3 {
    margin: 0;
  }
}

.close-button {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
}

.modal-body {
  padding: 16px;
  flex: 1 1 auto;
  overflow-y: auto;
  max-height: 60vh;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.modal-footer {
  padding: 16px;
  background: #f5f5f5;
  flex: 0 0 auto;
  border-top: 1px solid #ddd;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.modal-body::-webkit-scrollbar {
  width: 8px;
}

.modal-body::-webkit-scrollbar-thumb {
  background-color: rgba(0,0,0,0.2);
  border-radius: 4px;
}

.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter, .modal-leave-to {
  opacity: 0;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
  margin-top: 20px;
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

.email-container {
    background-color: #ffffff;
    max-width: 600px;
    padding: 20px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.email-header {
    text-align: center;
    margin-bottom: 20px;
}

.logo {
    max-width: 180px;
    height: auto;
    margin-bottom: 2em;
}

.email-content {
    font-size: 16px;
    line-height: 1.6;
}

.email-content p {
    margin-bottom: 15px;
}

.email-content a {
    color: #B4BE00;
    text-decoration: none;
    font-weight: bold;
}

.email-footer {
    font-size: 14px;
    color: #666666;
    margin-top: 20px;
    text-align: center;
}
</style>
