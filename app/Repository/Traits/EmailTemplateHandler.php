<?php

namespace App\Repository\Traits;

use App\Model\EmailTemplate;

trait EmailTemplateHandler
{

    /**
     * Replace email body key by items data.
     * @param $items
     * @param $emailBody
     * @return mixed
     */
    private function setEmailBody($items, $emailBody)
    {
        $patterns = array();
        $replacements = array();
        foreach ($items as $key => $value) {
            $patterns[] = '/{' . $key . '}/';
            $replacements[] = $value;
        }

        return preg_replace($patterns, $replacements, $emailBody);
    }

    /**
     * Get full process template.
     * @param null $code
     * @param $items
     * @return array
     */
    protected function getTemplate($code = null, $items)
    {
        if (session()->has('TENANT_DB_DATABASE'))
            switchToTenantDatabase();
        $template = EmailTemplate::where('template_code', $code)->first();

        $mailBody = $this->setEmailBody($items, $template['email_body']);
        if (!empty($mailBody) && sizeof($template) > 0) {
            return [
                'from' => $template['from'],
                'subject' => $template['subject'],
                'body' => $mailBody,
            ];
        } else {
            return [
                'from' => config('from.address'),
                'subject' => 'Invalid Email',
                'body' => 'Please inform Quitch about this invalid email.',
            ];
        }

    }
}