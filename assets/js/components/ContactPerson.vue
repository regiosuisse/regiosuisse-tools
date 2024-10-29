<template>
  <div class="contact-component">
    <div class="contact-component-form">
      <div class="contact-component-form-header">
        <h3 v-if="contact.id">Eintrag bearbeiten</h3>
        <h3 v-else>Eintrag erstellen</h3>

        <div class="contact-component-form-header-actions">
          <a class="button" @click="showPreview = true">Vorschau</a>
          <a
            class="button warning"
            @click="contact.isPublic = true"
            v-if="!contact.isPublic"
            >Privat</a
          >
          <a
            class="button success"
            @click="contact.isPublic = false"
            v-if="contact.isPublic"
            >Öffentlich</a
          >
          <a @click="locale = 'de'" class="button" :class="{ primary: locale === 'de' }"
            >DE</a
          >
          <a @click="locale = 'fr'" class="button" :class="{ primary: locale === 'fr' }"
            >FR</a
          >
          <a @click="locale = 'it'" class="button" :class="{ primary: locale === 'it' }"
            >IT</a
          >
          <a class="button error" @click="clickDelete()" v-if="contact.id">Löschen</a>
          <a class="button warning" @click="clickCancel()">Abbrechen</a>
          <a class="button primary" @click="clickSave()">Speichern</a>
          <a
            v-if="!$route.fullPath.includes('/edit')"
            class="button primary"
            @click="mergeAll(locale)"
            >Alle Daten übernehmen</a
          >
        </div>
      </div>

      <div class="contact-component-form-section" v-if="formErrors.length">
        <ul class="errors">
          <li class="error" v-for="error in formErrors">{{ error.message }}</li>
        </ul>
      </div>

      <div class="contact-component-form-section">
        <div class="contact-component-form-row">
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-2">
                <label for="gender">Anrede</label>
                <div class="select-wrapper">
                  <select
                    class="form-control"
                    v-model="contact.gender"
                    :class="{ 'field-changed': isFieldChanged('gender') }"
                  >
                    <option value="male">Herr</option>
                    <option value="female">Frau</option>
                    <option value="other">Keine Angabe</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <label for="academicTitle">Titel</label>
                <input
                  id="academicTitle"
                  type="text"
                  class="form-control"
                  v-model="contact.academicTitle"
                  :class="{ 'field-changed': isFieldChanged('academicTitle') }"
                />
              </div>
            </div>
          </div>
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-2" v-if="isFieldChanged('gender')">
                <label for="genderDiff">
                  <span class="material-icons" @click="mergeField('gender')"
                    >keyboard_backspace</span
                  >
                  Anrede
                </label>
                <select
                  id="genderDiff"
                  class="form-control"
                  v-model="diff.gender"
                  readonly
                >
                  <option value="male">Herr</option>
                  <option value="female">Frau</option>
                  <option value="other">Keine Angabe</option>
                </select>
              </div>
              <div class="col-md-4" v-if="isFieldChanged('academicTitle')">
                <label for="academicTitleDiff">
                  <span class="material-icons" @click="mergeField('academicTitle')"
                    >keyboard_backspace</span
                  >Titel</label
                >
                <input
                  id="academicTitleDiff"
                  type="text"
                  class="form-control"
                  v-model="diff.academicTitle"
                  :class="{ 'field-changed': isFieldChanged('academicTitle') }"
                />
              </div>
            </div>
          </div>
        </div>

        <div class="contact-component-form-row">
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-6">
                <label for="firstName">Vorname</label>
                <input
                  id="firstName"
                  type="text"
                  class="form-control"
                  v-model="contact.firstName"
                  :class="{ 'field-changed': isFieldChanged('firstName') }"
                />
              </div>
              <div class="col-md-6">
                <label for="lastName">Nachname</label>
                <input
                  id="lastName"
                  type="text"
                  class="form-control"
                  v-model="contact.lastName"
                  :class="{ 'field-changed': isFieldChanged('firstName') }"
                />
              </div>
            </div>
          </div>
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-6" v-if="isFieldChanged('firstName')">
                <label for="firstNameDiff">
                  <span class="material-icons" @click="mergeField('firstName')"
                    >keyboard_backspace</span
                  >
                  Vorname
                </label>
                <input
                  id="firstNameDiff"
                  type="text"
                  class="form-control"
                  :value="diff.firstName"
                  readonly
                />
              </div>
              <div class="col-md-6" v-if="isFieldChanged('lastName')">
                <label for="lastName">
                  <span class="material-icons" @click="mergeField('lastName')"
                    >keyboard_backspace</span
                  >Nachname</label
                >
                <input
                  id="lastName"
                  type="text"
                  class="form-control"
                  v-model="diff.lastName"
                  readonly
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Straße -->
        <div class="contact-component-form-row">
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-12">
                <label for="street">Strasse</label>
                <input
                  id="street"
                  type="text"
                  class="form-control"
                  v-model="contact.street"
                  :class="{ 'field-changed': isFieldChanged('street') }"
                />
              </div>
            </div>
          </div>
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-12" v-if="isFieldChanged('street')">
                <label for="streetDiff">
                  <span class="material-icons" @click="mergeField('street')"
                    >keyboard_backspace</span
                  >
                  Strasse
                </label>
                <input
                  id="streetDiff"
                  type="text"
                  class="form-control"
                  :value="diff.street"
                  readonly
                />
              </div>
            </div>
          </div>
        </div>

        <!-- PLZ und Ort -->
        <div class="contact-component-form-row">
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-4">
                <label for="zipCode">PLZ</label>
                <input
                  id="zipCode"
                  type="text"
                  class="form-control"
                  v-model="contact.zipCode"
                  :class="{ 'field-changed': isFieldChanged('zipCode') }"
                />
              </div>
              <div class="col-md-8" v-if="locale === 'de'">
                <label for="city">Ort</label>
                <input
                  id="city"
                  type="text"
                  class="form-control"
                  v-model="contact.city"
                  :class="{ 'field-changed': isFieldChanged('city') }"
                />
              </div>
              <div class="col-md-8" v-else>
                <label for="city">Ort (Übersetzung {{ locale.toUpperCase() }})</label>
                <input
                  id="city"
                  type="text"
                  class="form-control"
                  v-model="contact.translations[locale].city"
                  :class="{ 'field-changed': isFieldChanged('city', locale) }"
                />
              </div>
            </div>
          </div>
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-4" v-if="isFieldChanged('zipCode')">
                <label for="zipCodeDiff">
                  <span class="material-icons" @click="mergeField('zipCode')"
                    >keyboard_backspace</span
                  >
                  PLZ
                </label>
                <input
                  id="zipCodeDiff"
                  type="text"
                  class="form-control"
                  :value="diff.zipCode"
                  readonly
                />
              </div>
              <!-- Diff Section for City -->
              <div class="col-md-8" v-if="isFieldChanged('city', locale)">
                <label for="cityDiff">
                  <span class="material-icons" @click="mergeField('city', locale)"
                    >keyboard_backspace</span
                  >
                  Ort
                </label>
                <input
                  id="cityDiff"
                  type="text"
                  class="form-control"
                  :value="getDiffValue('city', locale)"
                  readonly
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Land und Kanton -->
        <div class="contact-component-form-row">
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-6">
                <label for="country">Land</label>
                <div class="select-wrapper">
                  <select
                    class="form-control"
                    @change="
                      contact.country =
                        getCountryById(parseInt($event.target.value)) || null;
                      contact.state = null;
                    "
                    :value="contact.country?.id"
                    :class="{ 'field-changed': isFieldChanged('country') }"
                  >
                    <option :value="null"></option>
                    <option v-for="country in countries" :value="country.id">
                      {{ country.name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="col-md-6" v-if="contact.country?.id === 1">
                <label for="province">Kanton</label>
                <div class="select-wrapper">
                  <select
                    class="form-control"
                    @change="
                      contact.state = getStateById(parseInt($event.target.value)) || null
                    "
                    :value="contact.state?.id"
                    :class="{ 'field-changed': isFieldChanged('state') }"
                  >
                    <option :value="null"></option>
                    <option v-for="state in states" :value="state.id">
                      {{ state.name }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-6" v-if="isFieldChanged('country')">
                <label for="countryDiff">
                  <span class="material-icons" @click="mergeField('country')"
                    >keyboard_backspace</span
                  >
                  Land
                </label>
                <select
                  id="countryDiff"
                  class="form-control"
                  :value="diff.country"
                  :class="{ 'field-changed': isFieldChanged('country') }"
                  readonly
                >
                  <option :value="null"></option>
                  <option v-for="country in countries" :value="country.id">
                    {{ country.name }}
                  </option>
                </select>
              </div>
              <div class="col-md-6" v-if="isFieldChanged('state')">
                <label for="stateDiff">
                  <span class="material-icons" @click="mergeField('state')"
                    >keyboard_backspace</span
                  >
                  Kanton
                </label>
                <input
                  id="stateDiff"
                  type="text"
                  class="form-control"
                  :value="diff.state?.name"
                  readonly
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Sprache -->
        <div class="contact-component-form-row">
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-6">
                <label for="language">Sprache</label>
                <div class="select-wrapper">
                  <select
                    class="form-control"
                    @change="
                      contact.language =
                        getLanguageById(parseInt($event.target.value)) || null
                    "
                    :value="contact.language?.id"
                    :class="{ 'field-changed': isFieldChanged('language') }"
                  >
                    <option :value="null"></option>
                    <option v-for="language in languages" :value="language.id">
                      {{ language.name }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-6" v-if="isFieldChanged('language')">
                <label for="languageDiff">
                  <span class="material-icons" @click="mergeField('language')"
                    >keyboard_backspace</span
                  >
                  Sprache
                </label>
                <select
                  id="languageDiff"
                  class="form-control"
                  :value="diff.language"
                  :class="{ 'field-changed': isFieldChanged('language') }"
                  readonly
                >
                  <option :value="null"></option>
                  <option v-for="language in languages" :value="language.id">
                    {{ language.name }}
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- E-Mail und Telefon -->
        <div class="contact-component-form-row">
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-6">
                <label for="email">E-Mail</label>
                <input
                  id="email"
                  type="email"
                  class="form-control"
                  v-model="contact.email"
                  :class="{ 'field-changed': isFieldChanged('email') }"
                />
              </div>
              <div class="col-md-6">
                <label for="phone">Telefon</label>
                <input
                  id="phone"
                  type="text"
                  class="form-control"
                  v-model="contact.phone"
                  :class="{ 'field-changed': isFieldChanged('phone') }"
                />
              </div>
            </div>
          </div>
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-6" v-if="isFieldChanged('email')">
                <label for="emailDiff">
                  <span class="material-icons" @click="mergeField('email')"
                    >keyboard_backspace</span
                  >
                  E-Mail
                </label>
                <input
                  id="emailDiff"
                  type="email"
                  class="form-control"
                  :value="diff.email"
                  readonly
                />
              </div>
              <div class="col-md-6" v-if="isFieldChanged('phone')">
                <label for="phoneDiff">
                  <span class="material-icons" @click="mergeField('phone')"
                    >keyboard_backspace</span
                  >
                  Telefon
                </label>
                <input
                  id="phoneDiff"
                  type="text"
                  class="form-control"
                  :value="diff.phone"
                  readonly
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Website -->
        <div class="contact-component-form-row">
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-6" v-if="locale === 'de'">
                <label for="website">Website</label>
                <input
                  id="website"
                  type="text"
                  class="form-control"
                  v-model="contact.website"
                  :class="{ 'field-changed': isFieldChanged('website') }"
                />
              </div>
              <div class="col-md-6" v-else>
                <label for="website"
                  >Website (Übersetzung {{ locale.toUpperCase() }})</label
                >
                <input
                  id="website"
                  type="text"
                  class="form-control"
                  v-model="contact.translations[locale].website"
                  :class="{ 'field-changed': isFieldChanged('website', locale) }"
                />
              </div>
            </div>
          </div>
          <div class="contact-component-form-section">
            <div class="row">
              <!-- Diff Section for Website -->
              <div class="col-md-6" v-if="isFieldChanged('website', locale)">
                <label for="websiteDiff">
                  <span class="material-icons" @click="mergeField('website', locale)"
                    >keyboard_backspace</span
                  >
                  Website
                </label>
                <input
                  id="websiteDiff"
                  type="text"
                  class="form-control"
                  :value="getDiffValue('website', locale)"
                  readonly
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Beschreibung -->
        <div class="contact-component-form-row">
          <div class="contact-component-form-section">
            <div class="row">
              <div class="col-md-12" v-if="locale === 'de'">
                <label for="description">Beschreibung</label>
                <ckeditor
                  id="description"
                  :editor="editor"
                  :config="editorConfig"
                  v-model="contact.description"
                  :class="{ 'field-changed': isFieldChanged('description') }"
                ></ckeditor>
              </div>
              <div class="col-md-12" v-else>
                <label for="description"
                  >Beschreibung (Übersetzung {{ locale.toUpperCase() }})</label
                >
                <ckeditor
                  id="description"
                  :editor="editor"
                  :config="editorConfig"
                  v-model="contact.translations[locale].description"
                  :class="{ 'field-changed': isFieldChanged('description', locale) }"
                ></ckeditor>
              </div>
            </div>
          </div>
          <div class="contact-component-form-section">
            <div class="row">
              <!-- Diff Section for Description -->
              <div class="col-md-12" v-if="isFieldChanged('description', locale)">
                <label for="descriptionDiff">
                  <span class="material-icons" @click="mergeField('description', locale)"
                    >keyboard_backspace</span
                  >
                  Beschreibung
                </label>
                <ckeditor
                  id="descriptionDiff"
                  :editor="editor"
                  :config="editorConfig"
                  v-model="diff.translations[locale].description"
                  readonly
                ></ckeditor>
              </div>
            </div>
          </div>
        </div>

        <div class="contact-component-form-section-group">
          <div class="row">
            <div class="col-md-6">
              <label for="topics">Kontakt Themen</label>
              <tag-selector
                v-if="contact && contact.topics !== undefined"
                id="topics"
                :model="contact.topics"
                :options="
                  topics.filter((topic) => !topic.context || topic.context === 'contact')
                "
                :searchType="'select'"
              ></tag-selector>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <label for="contactGroups">Kontaktgruppen</label>
              <tag-selector
                id="contactGroups"
                :model="contact.contactGroups"
                @change="addContactGroups($event)"
                :options="contactGroupOptions"
                :searchType="'select'"
                :isOptGroup="true"
              ></tag-selector>
            </div>
          </div>
        </div>
        <div class="contact-component-form-section-group">
          <div class="row">
            <div class="col-md-12">
              <label>Anstellungen</label>
              <div
                class="contact-group-component-form-section-group"
                v-for="(employment, index) in contact.employments"
              >
                <div class="row">
                  <div class="col-md-3">
                    <label for="company">Organisation</label>
                    <single-selector
                      id="company"
                      :value="employment.company"
                      @update="employment.company = $event || {}"
                      :options="contacts"
                    ></single-selector>
                  </div>
                  <div class="col-md-2" v-if="locale === 'de'">
                    <label for="employment">Funktion</label>
                    <input
                      id="employment"
                      type="text"
                      class="form-control"
                      v-model="employment.role"
                      :placeholder="translate('role', contact)"
                    />
                  </div>
                  <div class="col-md-2" v-else>
                    <label for="employment"
                      >Funktion (Übersetzung {{ locale.toUpperCase() }})</label
                    >
                    <input
                      id="employment"
                      type="text"
                      class="form-control"
                      v-model="employment.translations[locale].role"
                      :placeholder="translate('role', contact)"
                    />
                  </div>
                  <div class="col-md-1">
                    <label>Hauptadresse</label>
                    <input
                      id="officialAddress"
                      :name="'official-address-' + index"
                      type="checkbox"
                      :checked="contact.officialEmployment === employment || false"
                      :true-value="employment"
                      v-model="contact.officialEmployment"
                    />
                  </div>
                  <div class="col-md-2">
                    <label>&nbsp;</label>
                    <div
                      class="button warning"
                      @click="clickRemoveEmployment(employment.id, index)"
                    >
                      Anstellung entfernen
                    </div>
                  </div>
                </div>
              </div>
              <div class="contact-group-component-form-section-group">
                <div class="button success" @click="clickAddEmployment()">
                  Anstellung hinzufügen
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      class="contact-component-overlay"
      v-if="showPreview"
      @click="showPreview = false"
    >
      <EmbedContactsView
        @click.stop
        @clickClose="showPreview = false"
        :contact="contact"
        :officialEmployment="contact.officialEmployment || null"
        :locale="locale"
      ></EmbedContactsView>
    </div>

    <transition name="fade">
      <Modal v-if="modal" :config="modal"></Modal>
    </transition>
  </div>
</template>

<script>
import { mapState, mapGetters } from "vuex";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import TagSelector from "./TagSelector";
import SingleSelector from "./SingleSelector";
import Modal from "./Modal";
import EmbedContactsView from "./EmbedContactsView.vue";

export default {
  data() {
    return {
      locale: "de",
      diff: null,
      contact: {
        type: "person",
        companyName: "",
        specification: "",
        gender: "male",
        academicTitle: "",
        firstName: "",
        lastName: "",
        street: "",
        zipCode: "",
        city: "",
        country: null,
        state: null,
        language: null,
        email: "",
        phone: "",
        website: "",
        description: "",
        officialEmployment: null,
        parent: null,
        children: [],
        employments: [],
        companies: [],
        employees: [],
        contactGroups: [],
        topics: [],
        translations: {
          fr: {},
          it: {},
        },
        isPublic: false,
      },
      originalContactGroups: [],
      newContactGroups: [],
      showPreview: false,
      modal: null,
      editor: ClassicEditor,
      editorConfig: {
        basicEntities: false,
        toolbar: {
          items: [
            "heading",
            "|",
            "bold",
            "italic",
            "link",
            "|",
            "numberedList",
            "bulletedList",
            "insertTable",
            "|",
            "undo",
            "redo",
          ],
        },
      },
      formErrors: [],
    };
  },
  components: {
    EmbedContactsView,
    TagSelector,
    SingleSelector,
    Modal,
  },
  computed: {
    ...mapState({
      contacts: (state) => state.contacts.filtered,
      contactGroups: (state) => state.contactGroups.all,
      countries: (state) => state.countries.all,
      states: (state) => state.states.all,
      languages: (state) => state.languages.all,
      topics: (state) => state.topics.all,
      selectedInboxItem: (state) => state.inbox.item,
    }),
    ...mapGetters({
      getContactById: "contacts/getById",
      getCountryById: "countries/getById",
      getStateById: "states/getById",
      getLanguageById: "languages/getById",
      getEmploymentById: "employments/getById",
      getTopicById: "topics/getById",
    }),
    contactGroupOptions() {
      let contactGroupOptions = [];

      let parentGroups = this.contactGroups.filter((group) => !group.parent);

      for (let parentGroup of parentGroups) {
        parentGroup.children = this.contactGroups.filter(
          (group) => group.parent?.id === parentGroup.id
        );

        if (parentGroup.children.length) {
          contactGroupOptions.push(parentGroup);
        }
      }

      return contactGroupOptions;
    },
  },
  methods: {
    clickDelete() {
      this.modal = {
        title: "Eintrag löschen",
        description:
          "Sind Sie sicher dass Sie diesen Eintrag unwiderruflich löschen möchten?",
        actions: [
          {
            label: "Endgültig löschen",
            class: "error",
            onClick: () => {
              this.$store.dispatch("contacts/delete", this.contact.id).then(() => {
                this.$router.push("/contacts/person");
              });
            },
          },
          {
            label: "Abbrechen",
            class: "warning",
            onClick: () => {
              this.modal = null;
            },
          },
        ],
      };
    },
    clickCancel() {
      this.$router.push("/contacts/person");
    },
    async clickSave() {
      let contact = { ...this.contact };

      console.log(this.contact);
      // this somehow fixes the bug that translations wont be saved despite being set
      this.contact.translations = {
        fr: {
          ...this.contact.translations.fr,
        },
        it: {
          ...this.contact.translations.it,
        },
      };

      if (this.contact.id) {
        contact = await this.$store.dispatch("contacts/update", this.contact);
      } else {
        contact = await this.$store.dispatch("contacts/create", this.contact);
      }

      if (contact.officialEmployment) {
        let officialEmployment = { ...this.contact.officialEmployment };
        officialEmployment.employee = contact;

        for (let contactGroup of contact.contactGroups) {
          if (this.newContactGroups.find((group) => group.id === contactGroup.id)) {
            let group = this.contact.contactGroups.find(
              (group) => group.id === contactGroup.id
            );

            group.employments.push(officialEmployment);
            group.contacts.push(contact);

            this.$store.dispatch("contactGroups/update", group);
          }
        }
      }

      if (this.selectedInboxItem && this.selectedInboxItem.id) {
        await this.$store.dispatch("inbox/delete", this.selectedInboxItem.id);
        // Redirect to the inbox or any desired route after deletion
        this.$router.push("/inbox");
      } else {
        // Redirect to the contacts list if no inbox item was processed
        this.$router.push("/contacts/person");
      }
    },
    clickAddEmployment() {
      let employment = {
        position: null,
        company: null,
        employee: null,
        role: null,
        contactGroups: [],
        translations: {
          fr: {
            role: null,
          },
          it: {
            role: null,
          },
        },
      };

      this.contact.employments.push(employment);
    },
    clickRemoveEmployment(employmentId, index) {
      let employment = this.contact.employments.splice(index, 1)[0];

      if (this.contact.officialEmployment?.id === employmentId) {
        this.contact.officialEmployment = null;
      }
    },
    clickSetOfficialEmployment(event) {
      if (event.target.checked) {
        this.contact.officialEmployment = this.getEmploymentById(event.target.value);
      } else {
        this.contact.officialEmployment = null;
      }
    },
    async reload() {
      if (this.$route.params.id) {
        this.$store.commit("contacts/set", {});
        this.$store.commit("inbox/set", {});

        let contactsData = await this.$store.dispatch("contactsData/loadFiltered", {
          contactType: "employee",
          params: { ids: [this.$route.params.id] },
        });

        this.contact = { ...contactsData.contacts[0] };

        this.originalContactGroups = [...this.contact.contactGroups];

        this.contact.employments = contactsData.employments;

        this.contact.officialEmployment =
          this.contact.employments.find(
            (employment) => employment.id === this.contact.officialEmployment?.id
          ) || null;

        const inboxId = this.$route.params.inbox_id;

        if (inboxId) {
          // Load the inbox item using the store
          await this.$store.dispatch("inbox/load", inboxId);
          this.selectedInboxItem = this.$store.state.inbox.item;
          this.diff = this.selectedInboxItem.data.changes;
        }
      }
    },
    translate(property, context) {
      if (this.locale === "de") {
        return (
          context[property] ||
          context.translations.fr[property] ||
          context.translations.it[property]
        );
      }
      if (this.locale === "fr") {
        return (
          context.translations.fr[property] ||
          context[property] ||
          context.translations.it[property]
        );
      }
      if (this.locale === "it") {
        return (
          context.translations.it[property] ||
          context.translations.fr[property] ||
          context[property]
        );
      }
      return context[property];
    },
    addContactGroups(groups) {
      this.newContactGroups = groups.filter(
        (group) =>
          !this.originalContactGroups
            .map((originalGroup) => originalGroup.id)
            .includes(group.id)
      );
    },
    getDiffValue(field, locale = null) {
      if (!this.diff) return "";
      if (locale && this.diff.translations && this.diff.translations[locale]) {
        return this.diff.translations[locale][field] || "";
      } else {
        return this.diff[field] || "";
      }
    },

    isFieldChanged(field, locale = null) {
      if (!this.diff) return false;

      if (locale) {
        return (
          this.diff.translations &&
          this.diff.translations[locale] &&
          this.diff.translations[locale][field] !== undefined
        );
      } else {
        return this.diff[field] !== undefined;
      }
    },
    mergeField(field, locale = null) {
      const multilingualFields = ["website", "description", "city"];

      if (locale && multilingualFields.includes(field)) {
        if (!this.contact.translations[locale]) {
          this.contact.translations[locale] = {};
        }
        if (this.diff.translations && this.diff.translations[locale]) {
          this.contact.translations[locale][field] = this.diff.translations[locale][
            field
          ];
        }
        if (locale === "de") {
          this.contact[field] = this.diff.translations[locale][field];
        }
      } else if (multilingualFields.includes(field)) {
        this.contact[field] = this.diff[field];
      } else {
        this.contact[field] = this.diff[field];
      }

      if (field === "language") {
        this.contact.language = this.getLanguageById(this.diff.language) || null;
      } else if (field === "country") {
        this.contact.country = this.getCountryById(this.diff.country) || null;
      }
    },

    mergeAll() {
      const fields = [
        "academicTitle",
        "city",
        "country",
        "state",
        "language",
        "website",
        "description",
        "gender",
        "firstName",
        "lastName",
        "street",
        "zipCode",
        "phone",
        "email",
      ];

      const locales = ["de", "fr", "it"];

      fields.forEach((field) => {
        locales.forEach((locale) => {
          if (this.isFieldChanged(field, locale === "de" ? null : locale)) {
            this.mergeField(field, locale === "de" ? null : locale);
          }
        });
      });
    },
  },
  created() {
    this.reload();
    this.$store.dispatch("contacts/loadFiltered", { type: ["company"] });
  },
};
</script>
