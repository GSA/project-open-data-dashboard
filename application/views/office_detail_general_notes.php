
                <div class="general-notes">

                <?php
                $status_field_name = 'office_general';
                $note_field = "note_office_general";
                $note_data = (!empty($notes[$note_field])) ? $notes[$note_field] : '';

                if (!empty($notes[$note_field])) {
                    $note_data = $notes[$note_field];
                } else {
                    $note_data = $note_model;
                }
                ?>

                <?php if (empty($note_data->current->note)): ?>
                    <div class="note-heading">
                        <span class="note-metadata">
                            No general notes have been added yet
                        </span>                                    
                    </div>
                <?php endif; ?>

                <?php if (!empty($note_data->current->note) OR ( $this->session->userdata('permissions') == $permission_level)): ?>
                    <div class="edit-toggle">
                        <div class="edit-area"><?php echo $note_data->current->note_html; ?></div>
                        <div class="edit-raw hidden" data-fieldname="note_<?php echo $status_field_name ?>"><?php echo $note_data->current->note; ?></div>

                    <?php if (!empty($note_data->current->date) && !empty($note_data->current->author)): ?>
                            <div class="note-metadata">
                                Lasted edited on <?php echo $note_data->current->date; ?> by <?php echo $note_data->current->author; ?>
                            </div> 
                    <?php endif; ?>

                    <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                                <button class="btn btn-primary edit-button" type="button">Edit</button>                                
                    <?php endif; ?>
                        </div>
                <?php endif; ?>

                </div>

                <?php if ($this->session->userdata('permissions') == $permission_level) : ?>
                    <div  class="pull-right" style="margin : 1em 0;">                                
                        <button class="btn btn-default btn-xs" id="accShow">Show All Notes</button>
                        <button type="submit" class="btn btn-success btn-xs">Update</button> 
                    </div>  
                <?php endif; ?>
