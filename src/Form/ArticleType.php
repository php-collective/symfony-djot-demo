<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Article;
use PhpCollective\SymfonyDjot\Form\Type\DjotType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => ['placeholder' => 'Enter article title'],
            ])
            ->add('body', DjotType::class, [
                'label' => 'Body (Djot markup)',
                'attr' => [
                    'rows' => 10,
                    'placeholder' => "# My Article\n\nWrite your content using *Djot* markup...",
                ],
            ])
            ->add('comment', DjotType::class, [
                'label' => 'Comment (Djot with strict validation)',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Optional comment with strict Djot validation',
                ],
                'converter' => 'user_content',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Preview Article',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
