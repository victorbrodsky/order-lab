CREATE INDEX idx_ea22b84e63b59e5c
    ON public.scan_calllogentrymessage_patientlist USING btree
    (calllogentrymessage_id ASC NULLS LAST)
    TABLESPACE pg_default;

CREATE INDEX idx_d312ef9263b59e5c
    ON public.scan_calllogentrymessage_entrytag USING btree
    (calllogentrymessage_id ASC NULLS LAST)
    TABLESPACE pg_default;

CREATE INDEX idx_d267b39c33f7837
    ON public.calllog_calllogentrymessage_document USING btree
    (document_id ASC NULLS LAST)
    TABLESPACE pg_default;

CREATE INDEX idx_7f071c8fd6e2fadc
    ON public.scan_message_encounter USING btree
    (encounter_id ASC NULLS LAST)
    TABLESPACE pg_default;

CREATE INDEX idx_cf568530a40f1370
    ON public.scan_message_accession USING btree
    (accession_id ASC NULLS LAST)
    TABLESPACE pg_default;

CREATE INDEX idx_5eb89a4de9ed820c
    ON public.scan_message_block USING btree
    (block_id ASC NULLS LAST)
    TABLESPACE pg_default;

CREATE INDEX idx_6be23a97726d9566
    ON public.scan_messagecategory_formnode USING btree
    (formnode_id ASC NULLS LAST)
    TABLESPACE pg_default;






