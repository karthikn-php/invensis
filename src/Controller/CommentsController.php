<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\I18n\Date;
use Cake\I18n\Time;

/**
 * Comments Controller
 *
 * @method \App\Model\Entity\Comment[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CommentsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function lists($id)
    {
        if ($this->request->is('get')) {
            $this->loadModel('Posts');
            $postAndComments = $this->Posts->get($id, [
                'contain' => ['Comments'],
            ]);
            return $this->response
                ->withType('json')
                ->withStringBody(
                    json_encode([
                        'post_and_comments' => $postAndComments
                    ])
                );
        }
        return $this->response
            ->withStatus(405, 'Method Not Allowed')
            ->withType('json')
            ->withStringBody(
                json_encode([
                    'message' => 'The Requested Method Not Allowed'
                ])
            );
    }

    /**
     * View method
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        if ($this->request->is('get')) {
            $comment = $this->Comments->get($id, [
                'contain' => [],
            ]);
            return $this->response
                ->withType('json')
                ->withStringBody(
                    json_encode([
                        'comment' => $comment
                    ])
                );
        }
        return $this->response
            ->withStatus(405, 'Method Not Allowed')
            ->withType('json')
            ->withStringBody(
                json_encode([
                    'message' => 'The Requested Method Not Allowed'
                ])
            );
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $comment    =   $this->Comments->newEmptyEntity();
        $requestData=   $this->request->getData();
        if ($this->request->is('post')) {
            $this->loadModel('Users');
            $userData   =   $this->Users->find()->select(['id'])->orderDesc('id')->first()->toArray();
            if (!empty($userData)) {
                $requestData['user_id']     = rand(1, $userData['id']);
            }
            $requestData['created_at'] = Time::now()->format(Date::DEFAULT_TO_STRING_FORMAT);
            $comment = $this->Comments->patchEntity($comment, $requestData);
            $errors = [];
            if (!$comment->hasErrors()) {
                try {
                    $this->loadModel('Posts');
                    $postArray = $this->Posts->find()->select(['id'])->where(['id' => $requestData['post_id']])->first();
                    if (!empty($postArray)) {
                        $this->Comments->save($comment);
                        return $this->response
                            ->withType('json')
                            ->withStringBody(
                                json_encode([
                                    'message' => 'The comment has been saved.'
                                ])
                            );
                    }
                    else {
                        return $this->response
                            ->withType('json')
                            ->withStringBody(
                                json_encode([
                                    'message' => 'The comment could not be saved since post could not found.'
                                ])
                            );
                    }

                } catch (\Exception $exception) {
                    \Cake\Log\Log::error(print_r($requestData, true));
                    \Cake\Log\Log::error($exception->getMessage());
                    return $this->response
                        ->withType('json')
                        ->withStringBody(
                            json_encode([
                                'message' => 'The comment could not be saved. Please, try again.'
                            ])
                        );
                }
            } else {
                foreach ($comment->getErrors() as $fieldName => $fieldHasErrors) {
                    $errors[$fieldName] = implode(', ', $fieldHasErrors);
                }
            }
            return $this->response
                ->withType('json')
                ->withStringBody(
                    json_encode([
                        'errors' => $errors,
                        'message' => 'The comment could not be saved. Please, try again.'
                    ])
                );
        }
        return $this->response
            ->withStatus(405, 'Method Not Allowed')
            ->withType('json')
            ->withStringBody(
                json_encode([
                    'message' => 'The Requested Method Not Allowed'
                ])
            );
    }

    /**
     * Edit method
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $comment = $this->Comments->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $comment = $this->Comments->patchEntity($comment, $this->request->getData());
            if ($this->Comments->save($comment)) {
                $this->Flash->success(__('The comment has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The comment could not be saved. Please, try again.'));
        }
        $this->set(compact('comment'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        if ($this->request->is('delete')) {
            $comment = $this->Comments->get($id);
            if ($this->Comments->delete($comment)) {
                return $this->response
                    ->withType('json')
                    ->withStringBody(
                        json_encode([
                            'message' => 'The comment has been deleted.'
                        ])
                    );
            } else {
                return $this->response
                    ->withType('json')
                    ->withStringBody(
                        json_encode([
                            'message' => 'The comment could not be deleted. Please, try again.'
                        ])
                    );
            }
        }
        return $this->response
            ->withStatus(405, 'Method Not Allowed')
            ->withType('json')
            ->withStringBody(
                json_encode([
                    'message' => 'The Requested Method Not Allowed'
                ])
            );
    }
}
